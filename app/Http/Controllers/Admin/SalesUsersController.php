<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SalesUsersController extends Controller
{
    public function index()
    {
        $users = SalesUser::latest()->paginate(20);
        return view('admin.sales_users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.sales_users.create', ['nextCode' => self::nextEmpcode()]);
    }

    /**
     * The next code in the SB#### series, so the admin never has to look up
     * what the last one was. Only codes that actually follow the pattern are
     * counted, and it falls back to SB1001 on an empty table.
     */
    public static function nextEmpcode(string $prefix = 'SB'): string
    {
        $highest = SalesUser::where('empcode', 'like', $prefix . '%')
            ->get()
            ->map(fn ($u) => preg_match('/^' . $prefix . '(\d+)$/i', $u->empcode, $m) ? (int) $m[1] : null)
            ->filter()
            ->max();

        return $prefix . (($highest ?? 1000) + 1);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules() + [
            'empcode'  => 'required|string|max:20|unique:sales_users,empcode',
            'password' => 'required|string|min:6|confirmed',
        ], $this->messages());

        $user = SalesUser::create($this->details($request) + [
            'password' => Hash::make($request->password),
        ]);

        $this->saveImages($request, $user);

        return redirect()->route('admin.sales_users.index')
            ->withAlert(['success', 'Sales user created successfully.']);
    }

    /** Everything a sales user record holds, apart from code and password. */
    private function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'email'          => 'nullable|email|max:100',
            'phone'          => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'alt_phone'      => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'designation'    => 'nullable|string|max:60',
            'joined_on'      => 'nullable|date',
            'address'        => 'nullable|string|max:255',
            'area'           => 'nullable|string|max:120',
            'city'           => 'nullable|string|max:60',
            'state'          => 'nullable|string|max:80',
            'pincode'        => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
            'aadhaar_no'     => ['nullable', 'regex:/^[0-9]{12}$/'],
            'pan_no'         => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'account_holder' => 'nullable|string|max:120',
            'bank_name'      => 'nullable|string|max:120',
            'bank_account'   => ['nullable', 'regex:/^[0-9]{6,20}$/'],
            'bank_ifsc'      => ['nullable', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
            'bank_branch'    => 'nullable|string|max:120',
            'avatar'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'aadhaar_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'pan_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    private function messages(): array
    {
        return [
            'phone.regex'        => 'Enter a 10-digit Indian mobile number.',
            'alt_phone.regex'    => 'Enter a 10-digit Indian mobile number.',
            'pincode.regex'      => 'Enter a 6-digit pincode.',
            'aadhaar_no.regex'   => 'An Aadhaar number is 12 digits.',
            'pan_no.regex'       => 'A PAN is 10 characters, e.g. AAAAA0000A.',
            'bank_account.regex' => 'An account number is 6 to 20 digits.',
            'bank_ifsc.regex'    => 'An IFSC looks like HDFC0001234.',
        ];
    }

    private function details(Request $request): array
    {
        return [
            'name'           => $request->name,
            'empcode'        => strtoupper($request->empcode),
            'email'          => $request->email,
            'phone'          => $request->phone,
            'alt_phone'      => $request->alt_phone,
            'designation'    => $request->designation,
            'joined_on'      => $request->joined_on,
            'address'        => $request->address,
            'area'           => $request->area,
            'city'           => $request->city ?: 'Jaipur',
            'state'          => $request->state,
            'pincode'        => $request->pincode,
            'aadhaar_no'     => $request->aadhaar_no,
            'pan_no'         => $request->pan_no ? strtoupper($request->pan_no) : null,
            'account_holder' => $request->account_holder,
            'bank_name'      => $request->bank_name,
            'bank_account'   => $request->bank_account,
            'bank_ifsc'      => $request->bank_ifsc ? strtoupper($request->bank_ifsc) : null,
            'bank_branch'    => $request->bank_branch,
            'status'         => $request->has('status') ? 1 : 0,
        ];
    }

    /**
     * Photo and ID scans. Each is converted to WebP under a fresh name, and
     * the file it replaces is removed.
     */
    private function saveImages(Request $request, SalesUser $user): void
    {
        $targets = [
            'avatar'        => SalesUser::AVATAR_DIR,
            'aadhaar_image' => SalesUser::DOC_DIR,
            'pan_image'     => SalesUser::DOC_DIR,
        ];

        foreach ($targets as $field => $dir) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $full = public_path($dir);

            if (!is_dir($full) && !@mkdir($full, 0775, true)) {
                continue;
            }

            $old  = $user->{$field};
            $name = 'sg_' . $user->id . '_' . $field . '_' . time() . '.webp';

            try {
                $this->toWebp($request->file($field)->getRealPath(), $full . '/' . $name);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Sales user image failed', [
                    'field' => $field, 'error' => $e->getMessage(),
                ]);
                continue;
            }

            $user->update([$field => $name]);

            if ($old && is_file($full . '/' . $old)) {
                @unlink($full . '/' . $old);
            }
        }
    }

    /** Scale down to at most 1000px and save as WebP. */
    private function toWebp(string $src, string $dest, int $max = 1000, int $quality = 82): void
    {
        $info = @getimagesize($src);

        if (!$info) {
            copy($src, $dest);
            return;
        }

        [$w, $h] = $info;

        $image = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png'  => @imagecreatefrompng($src),
            'image/webp' => @imagecreatefromwebp($src),
            'image/gif'  => @imagecreatefromgif($src),
            default      => null,
        };

        if (!$image) {
            copy($src, $dest);
            return;
        }

        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagewebp($out, $dest, $quality);

        imagedestroy($image);
        imagedestroy($out);
    }

    public function edit(SalesUser $salesUser)
    {
        return view('admin.sales_users.create', ['user' => $salesUser, 'edit' => true]);
    }

    public function update(Request $request, SalesUser $salesUser)
    {
        $request->validate($this->rules() + [
            'empcode'  => 'required|string|max:20|unique:sales_users,empcode,' . $salesUser->id,
            'password' => 'nullable|string|min:6|confirmed',
        ], $this->messages());

        $data = $this->details($request);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $salesUser->update($data);

        $this->saveImages($request, $salesUser);

        return redirect()->route('admin.sales_users.index')
            ->withAlert(['success', 'Sales user updated successfully.']);
    }

    public function destroy(SalesUser $salesUser)
    {
        $salesUser->delete();
        return redirect()->route('admin.sales_users.index')
            ->withAlert(['success', 'Sales user deleted.']);
    }

    public function toggleStatus(SalesUser $salesUser)
    {
        $salesUser->update(['status' => $salesUser->status ? 0 : 1]);
        return back()->withAlert(['success', 'Status updated.']);
    }
}
