<?php

namespace App\Http\Traits;

use App\Http\Traits\FileHandler;
use App\Models\Setting;
use App\Models\Theme;
use App\Support\ContentHtml;
use App\Support\Images\WebpImage;
use HTMLPurifier;
use Illuminate\Http\Request;

trait Template{

	use FileHandler;

	protected static $validate = [];
	protected static $filenames = [];
	protected static $filePath = '';
	protected static $imageFilePath = '';
	protected static $elementValue = [];
	protected static $existedElement = null;

/**
 * Make sure an upload directory exists, once, right before something is
 * written into it.
 *
 * Returns false rather than throwing: a picture that could not be stored
 * should cost the admin that picture, not the whole save. The reason is
 * logged so the missing permission is findable afterwards.
 */
protected static function ensureUploadDirectory(string $relativePath): bool
{
    $absolute = public_path($relativePath);

    if (is_dir($absolute)) {
        return is_writable($absolute);
    }

    if (!@mkdir($absolute, 0755, true) && !is_dir($absolute)) {
        \Illuminate\Support\Facades\Log::error('Theme upload directory could not be created', [
            'path'  => $absolute,
            'error' => error_get_last()['message'] ?? null,
        ]);

        return false;
    }

    return true;
}

public function elementSave(Request $request, $id = null)
{
    $key = $request->key;
    $setting = Setting::first();
    $theme = $setting->theme;
    $tempArr = $this->$theme();

    if (!array_key_exists($key, $tempArr)) {
        abort(404);
    }

    $item = $tempArr[$key];
    $fields = $item[$request->type];
    self::$filePath = 'assets/' . $setting->theme . '/files/' . $key . '/';
    self::$imageFilePath = 'assets/' . $setting->theme . '/images/' . $key . '/';

    if ($id) {
        $element = Theme::findOrFail($id);
    } else {
        if (self::isMultiple($request->type)) {
            $element = new Theme();
        } else {
            $element = Theme::firstOrNew([
                'name' => $key,
                'theme_name' => $setting->theme,
                'is_multiple' => self::isMultiple($request->type),
            ]);
        }
    }

    $oldContent = $element->content ? (array) $element->content : [];
    $content = [];

    /*
     * Upload directories are NOT created here.
     *
     * Both of them used to be created on every save, so editing nothing but a
     * blog's text still needed write permission on public/assets — and on a
     * server that does not grant it, `mkdir(): Permission denied` took down a
     * save that was never going to touch a file. They are now created lazily,
     * by ensureUploadDirectory(), at the point an upload actually happens.
     */

    foreach ($fields as $fieldKey => $data) {
        if ($fieldKey === 'is_modal') {
            continue;
        }

        $type = $data['type'] ?? null;

        // Text, textarea and select fields
        if (in_array($type, ['text', 'textarea', 'select'], true)) {
            $value = $request->input($fieldKey);

            // Fields marked `rich` are Summernote editors — blog bodies being
            // the big one. Their content is pasted out of Word, so it is
            // normalised on the way in for the same reason category content
            // is: otherwise every blog post arrives with its own font sizes,
            // its own colours, and tables that fall off the edge of a phone.
            // See App\Support\ContentHtml.
            if (!empty($data['rich'])) {
                $value = ContentHtml::clean($value);
            }

            $content[$fieldKey] = $value;
        }
        // Checkbox field
        elseif ($type === 'checkbox') {
            $content[$fieldKey] = $request->has($fieldKey) ? '1' : '0';
        }
        // File field
        elseif ($type === 'file') {
            // Image field
            if (!empty($data['is_image'])) {
                // Allow-list of extensions for this field: configured `mimes` if
                // present, otherwise the trait-wide default image allow-list.
                $allowedImageExt = !empty($data['mimes'])
                    ? array_map('strtolower', array_map('trim', explode(',', $data['mimes'])))
                    : self::ALLOWED_IMAGE_EXTENSIONS;

                // Multiple image tabhi chalega jab configuration me:
                // 'multiple' => true diya ho.
                $isMultipleImage = !empty($data['multiple']);

                if ($isMultipleImage) {
                    $savedImages = [];

                    // Form me preserve ki gayi old images
                    $oldImages = $request->input('old_images_' . $fieldKey, []);
                    $oldLinks = $request->input('old_links_' . $fieldKey, []);
                    if (!is_array($oldImages)) {
                        $oldImages = [];
                    }
                    if (!is_array($oldLinks)) {
                        $oldLinks = [];
                    }

                    foreach ($oldImages as $index => $oldImg) {
                        if (!empty($oldImg)) {
                            $savedImages[] = [
                                'image' => $oldImg,
                                'link' => $oldLinks[$index] ?? '',
                            ];
                        }
                    }

                    // New multiple images
                    if ($request->hasFile($fieldKey)) {
                        $uploadedImages = $request->file($fieldKey);
                        $uploadedImages = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                        $newLinks = $request->input('new_links_' . $fieldKey, []);
                        if (!is_array($newLinks)) {
                            $newLinks = [];
                        }

                        foreach ($uploadedImages as $index => $image) {
                            if (!$image || !$image->isValid()) {
                                continue;
                            }

                            $extension = strtolower($image->getClientOriginalExtension());
                            if (!in_array($extension, $allowedImageExt, true)) {
                                continue;
                            }

                            if (!self::ensureUploadDirectory(self::$imageFilePath)) {
                                continue;
                            }

                            /*
                             * Stored as WebP — a banner slide is one of the
                             * heaviest images on the homepage and every
                             * visitor downloads all of them. See WebpImage.
                             *
                             * A file that cannot be decoded skips this slot
                             * rather than aborting the save: the rest of the
                             * banner, and the title and subtitle typed with
                             * it, must still go in.
                             */
                            try {
                                $imageName = WebpImage::store(
                                    $image,
                                    self::$imageFilePath,
                                    uniqid('', true) . '_' . time()
                                );
                            } catch (\Throwable $e) {
                                report($e);
                                continue;
                            }

                            $savedImages[] = [
                                'image' => $imageName,
                                'link' => $newLinks[$index] ?? '',
                            ];
                        }
                    }

                    $content[$fieldKey] = json_encode($savedImages, JSON_UNESCAPED_SLASHES);
                }
                // Single image
                else {
                    if ($request->hasFile($fieldKey)) {
                        $image = $request->file($fieldKey);
                        $extension = $image ? strtolower($image->getClientOriginalExtension()) : null;

                        if ($image && $image->isValid()
                            && in_array($extension, $allowedImageExt, true)
                            && self::ensureUploadDirectory(self::$imageFilePath)) {
                            // Stored as WebP; see WebpImage. A file that
                            // cannot be decoded leaves the existing image in
                            // place instead of taking the whole save down.
                            try {
                                $imageName = WebpImage::store(
                                    $image,
                                    self::$imageFilePath,
                                    uniqid('', true) . '_' . time()
                                );
                            } catch (\Throwable $e) {
                                report($e);
                                $content[$fieldKey] = $oldContent[$fieldKey] ?? null;
                                continue;
                            }

                            // New image successfully save hone ke baad old image delete hogi.
                            $oldImage = $oldContent[$fieldKey] ?? null;
                            if (!empty($oldImage)) {
                                $oldImagePath = public_path(self::$imageFilePath . $oldImage);
                                if (is_file($oldImagePath)) {
                                    @unlink($oldImagePath);
                                }
                            }

                            $content[$fieldKey] = $imageName;
                        } else {
                            // Invalid upload hone par old image preserve.
                            $content[$fieldKey] = $oldContent[$fieldKey] ?? null;
                        }
                    } else {
                        // Edit me new image select nahi ki, to old image preserve rahegi.
                        $content[$fieldKey] = $oldContent[$fieldKey] ?? null;
                    }
                }
            }
            // Normal non-image file
            else {
                if ($request->hasFile($fieldKey)) {
                    $file = $request->file($fieldKey);
                    $extension = $file ? strtolower($file->getClientOriginalExtension()) : null;
                    $allowedFileExt = !empty($data['mimes'])
                        ? array_map('strtolower', array_map('trim', explode(',', $data['mimes'])))
                        : null;
                    $isSafeExtension = $extension !== null
                        && $extension !== ''
                        && !in_array($extension, self::BLOCKED_EXTENSIONS, true)
                        && ($allowedFileExt === null || in_array($extension, $allowedFileExt, true));

                    if ($file && $file->isValid() && $isSafeExtension
                        && self::ensureUploadDirectory(self::$filePath)) {
                        $fileName = uniqid('', true) . '_' . time() . '.' . $extension;

                        // Pehle new file upload hogi.
                        $file->move(public_path(self::$filePath), $fileName);

                        // New file successfully save hone ke baad old file delete hogi.
                        $oldFile = $oldContent[$fieldKey] ?? null;
                        if (!empty($oldFile)) {
                            $oldFilePath = public_path(self::$filePath . $oldFile);
                            if (is_file($oldFilePath)) {
                                @unlink($oldFilePath);
                            }
                        }

                        $content[$fieldKey] = $fileName;
                    } else {
                        $content[$fieldKey] = $oldContent[$fieldKey] ?? null;
                    }
                } else {
                    // New file select nahi ki, to old file preserve rahegi.
                    $content[$fieldKey] = $oldContent[$fieldKey] ?? null;
                }
            }
        }
    }

    // Blog aur useful links slug
    if (in_array($key, ['blog', 'usefull_links'], true) && !empty($content['title'])) {
        $content['slug'] = \Illuminate\Support\Str::slug($content['title']);
    }

    /*
     * dateModified ke liye apna timestamp.
     *
     * Theme ka `updated_at` is kaam ka nahi hai: blogDetails() har page
     * view par view-counter badha kar save() karta hai, jisse updated_at
     * har visitor hit par aaj ki date ho jata hai. Us par "Last Updated"
     * dikhate to har blog roz "aaj update hua" dikhta — freshness ka
     * jhoota signal. Ye field sirf yahan set hoti hai, yaani sirf jab
     * admin ne content actually save kiya ho.
     */
    if ($key === 'blog') {
        $content['content_updated_at'] = now()->toDateTimeString();
    }

    $element->name = $key;
    $element->content = $content;
    $element->is_multiple = self::isMultiple($request->type);
    $element->theme_name = $setting->theme;
    $element->save();

    if (collect($fields)->contains(fn ($data) => !empty($data['rich']))) {
        ContentHtml::flushPublicPageCache();
    }

    $alert = ['success', 'Element saved successfully'];
    return back()->withAlert($alert);
}
	//Remove data
	public function remove($id){
		$element = Theme::findOrFail($id);
		$element->delete();

		$alert = ['success','Element deleted successfully'];
		return back()->withAlert($alert);
	}



	//Validation
	protected function validation($request,$data,$key,$validation = []){
			if (@$data['required'] && $data['required'] == true) {
				if (($data['type'] == 'file') && self::$existedElement->id) {
					$validation[$key][] = 'nullable';
				}else{
					$validation[$key][] = 'required';
				}
			}
			if (@$data['type'] == 'file') {
				if (@$data['is_image'] == true) {
					$validation[$key][] = 'image';
				}
				$validation[$key][] = 'mimes:'.$data['mimes'];
			}
			if (@$data['type'] == 'select') {
				$validation[$key][] = 'in:'.implode(',', $data['options']);
			}
			if (@$validation[$key]) {
				$validation[$key] = implode('|', $validation[$key]);
				static::$validate = array_merge(static::$validate,$validation);
			}
	}


	//HTML Purifier
	protected function purify($request,$data,$key){
		$purifier = new HTMLPurifier();
		if(@$data['type'] != 'file'){
			$purifier->purify($request->$key);
		}
	}

	//File upload
	protected function fileUpload($request,$data,$key){
		if(@$data['type'] == 'file'){
			$element = self::$existedElement;
			$oldFile = null;
			if ($element->id) {
				$oldFile = $element->content->$key;
			}
			if ($request->hasFile($key)) {
				$type = 'others';
				$size = null;
				$path = self::$filePath;
				if (@$data['is_image'] == true) {
					$type = 'image';
					$size = $data['size'];
					$path = self::$imageFilePath;
				}
				$imageData = [
	                'file'=>$request->$key,
	                'path'=>$path,
	                'size'=>$size,
	                'prevFile'=>$oldFile,
	            ];
	            $filename[$key] = $this->uploadFile($type,$imageData);
			}else{
	            $filename[$key] = $oldFile;
			}
	        self::$filenames = array_merge(self::$filenames,$filename);
		}
	}


	//create or update
	protected function createItemValue($request,$data,$key){
		if (@$data['type'] == 'checkbox') {
			$item[$key] = @$request->$key ? '1' : '0';
		}elseif (@$data['type'] == 'file') {
			// $item[$key] =
			if (array_key_exists($key, self::$filenames)) {
				$item[$key] = self::$filenames[$key];
			}
		}else{
			$item[$key] = $request->$key;
		}
		self::$elementValue = array_merge(self::$elementValue,$item);
	}


	//Field loop
	protected function fieldLoop($fields,$request,$method){
		foreach ($fields as $key => $data) {
			$this->$method($request,$data,$key);
		}
	}


	//directory360 Template
	public function justfind(){
		$arr = [
			'banner'=>[
				'name'=>'Banner Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'multiple'=>true,
						'size'=>'1920x1100'
					],
				],
			],
			'inner_banner'=>[
				'name'=>'Inner Banner',
				'single_item'=>[
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'size'=>'1920x1100'
					],
				],
			],
			'category'=>[
				'name'=>'Category Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
			],
			'latest_listing'=>[
				'name'=>'Latest Listing Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
			],
			'cta'=>[
				'name'=>'CTA Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'button_name'=>[
						'type'=>'text',
						'required'=>true,
					],
					'button_url'=>[
						'type'=>'text',
						'required'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'size'=>'1920x1250'
					],
				],
			],
			'featured_listing'=>[
				'name'=>'Featured Listing Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
			],
			'how_work'=>[
				'name'=>'How it work Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'size'=>'1920x1250'
					],
				],
				'multiple_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'icon'=>[
						'type'=>'text',
						'required'=>true,
					],
					'is_modal'=>true
				]
			],
			'contact'=>[
				'name'=>'Contact Section',
                'single_item'=>[],
				'multiple_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'content'=>[
						'type'=>'text',
						'required'=>true,
					],
					'icon'=>[
						'type'=>'text',
						'required'=>true,
					],
					'is_modal'=>true
				]
			],
			'location'=>[
				'name'=>'Location Section',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
			],
            'faq'=>[
                'name'=>'Faq Section',
                'single_item'=>[
                    'title'=>[
                        'type'=>'text',
                        'required'=>true,
                    ],
                    'sub_title'=>[
                        'type'=>'text',
                        'required'=>true,
                    ],
                ],
                'multiple_item'=>[
                    'question'=>[
                        'type'=>'text',
                        'required'=>true,
                    ],
                    'answer'=>[
                        'type'=>'text',
                        'required'=>true,
                    ],
                    'is_modal'=>true
                ]
            ],
			'site_contact'=>[
				'name'=>'Site Contact',
				'single_item'=>[
					'phone'=>[
						'type'=>'text',
						'required'=>true,
					],
					'phone_2'=>[
						'type'=>'text',
						'required'=>false,
					],
					'email'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
			],
			'footer'=>[
				'name'=>'Footer Content',
				'single_item'=>[
					'description'=>[
						'type'=>'textarea',
						'required'=>true,
						'rich'=>false,
					],
					'subscribe_content'=>[
						'type'=>'text',
						'required'=>true
					],
				],
			],
			'login'=>[
				'name'=>'Login Page',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'login_content'=>[
						'type'=>'text',
						'required'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'size'=>'1920x1100'
					],
				],
			],
			'register'=>[
				'name'=>'Register Page',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'register_content'=>[
						'type'=>'text',
						'required'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						'mimes'=>'jpg,jpeg,png',
						'is_image'=>true,
						'size'=>'1920x1100'
					],
				],
			],
			'blog'=>[
				'name'=>'News & Blog',
				'single_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,	
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					]
				],
				'multiple_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'sub_title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'content'=>[
						'type'=>'textarea',
						'required'=>true,
						'rich'=>true,
					],
					'image'=>[
						'type'=>'file',
						'required'=>true,
						
						'is_image'=>true,
						'size'=>'680x460'
					],
					
					 'meta_title'=>[
        'type'=>'text',
        'required'=>false,
    ],
    'meta_keywords'=>[
        'type'=>'textarea',
        'required'=>false,
    ],
    'meta_description'=>[
        'type'=>'textarea',
        'required'=>false,
    ],

    /*
     * Author byline ke fields. Blog posts par koi author naam ya
     * credentials nahi the, jo E-E-A-T ka gap tha — Google ke liye
     * "kisne likha aur kis authority se" ka koi signal hi nahi tha.
     * Dono optional hain: khaali chhodne par blog_details.blade.php
     * publisher (site) ko hi author dikha deta hai, isliye purane
     * blogs bina edit kiye waise ke waise chalte rehte hain.
     */
    'author_name'=>[
        'type'=>'text',
        'required'=>false,
    ],
    'author_designation'=>[
        'type'=>'text',
        'required'=>false,
    ],

					'is_modal'=>false
				],
			],
			'usefull_links'=>[
				'name'=>'Usefull Links',
                'single_item'=>[],
				'multiple_item'=>[
					'title'=>[
						'type'=>'text',
						'required'=>true,
					],
					'content'=>[
						'type'=>'textarea',
						'required'=>true,
						'rich'=>true,
					],
					'is_modal'=>false
				],
			],
		];

		return $arr;
	}

public function servicebazzar()
{
    return $this->justfind();
}

	//is multiple
	private static function isMultiple($type){
		if ($type == 'single_item') {
			$is_multiple = 0;
		}else{
			$is_multiple = 1;
		}
		return $is_multiple;
	}

	public function getThemeItem($key,$isMultiple){
		$setting = Setting::first();
		$theme = $setting->theme;
		if ($isMultiple) {
			$item = Theme::where('name',$key)->where('theme_name',$setting->theme)->where('is_multiple',1)->get();
		}else{
			$item = Theme::where('name',$key)->where('theme_name',$setting->theme)->where('is_multiple',0)->first();
		}
		return $item;
	}
}
