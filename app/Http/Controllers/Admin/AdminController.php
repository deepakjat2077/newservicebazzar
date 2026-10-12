<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\Listing;
use App\Models\LoginHistory;
use App\Models\Payment;
use App\Models\Enquiry;
use App\Models\GuestMessage;
use App\Models\Admin;
use App\Models\Role;
use App\Models\CategoryHeading;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
	use FileHandler;

public function dashboard(Request $request){

    $admin = auth()->guard('admin')->user();

    /*
     * The dashboard is where login lands, so a role without dashboard.view
     * must be forwarded to whatever it *can* open rather than shown a 403
     * with no way forward.
     */
    if (!$admin->hasPermission('dashboard.view')) {
        $fallback = $admin->firstAccessibleRoute();

        if ($fallback) {
            return redirect()->route($fallback);
        }

        abort(403, 'Your role has no sections enabled. Ask a super admin to grant permissions.');
    }

    $data['admin'] = $admin;
    $data['totalUsers'] = User::count();
    $data['activeUsers'] = User::where('status',1)->where('email_verified',1)->count();
    $data['emailUnverified'] = User::where('status',1)->where('email_verified',0)->count();
    $data['inActiveUsers'] = $data['totalUsers'] - $data['activeUsers'];

    $data['monthDeposits'] = Payment::where('status', 1)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->format('m'))
        ->selectRaw("DATE_FORMAT(created_at, '%d') as days")
        ->selectRaw("SUM(amount) as amount")
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d')"))
        ->get();

    // Last calendar month of the right year (whereMonth alone also summed the same month of earlier years).
    $data['prevDepo'] = Payment::where('status',1)
        ->whereYear('created_at', now()->subMonthNoOverflow()->year)
        ->whereMonth('created_at', now()->subMonthNoOverflow()->format('m'))
        ->sum('amount');
    $data['prevMonthName'] = now()->subMonthNoOverflow()->format('F');

    $prevDepo = $data['prevDepo'] ? $data['prevDepo'] : 1;
    $data['diffDepo'] = $data['monthDeposits']->sum('amount')
        ? round(($data['monthDeposits']->sum('amount') / $prevDepo * 100) - 100, 1)
        : 0;

    $data['pendingDeposits'] = Payment::where('status',2)->sum('amount');
    $data['rejectedDeposits'] = Payment::where('status',3)->sum('amount');
    $data['deposits'] = Payment::where('status',1)->sum('amount');

    $data['pendingListings'] = Listing::where('status',0)->count();
    $data['rejectedListings'] = Listing::where('status',2)->count();
    $data['listings'] = Listing::where('status',1)->count();

    $data['browsers'] = LoginHistory::groupBy('browser')
        ->selectRaw("count(*) as browserCount")
        ->selectRaw("browser as name")
        ->orderBy('browserCount','desc')
        ->get();

    $data['totalBrowserPercent'] = 0;

    $data['countries'] = LoginHistory::groupBy('country')
        ->selectRaw("count(*) as countryCount")
        ->selectRaw("country as name")
        ->orderBy('countryCount','desc')
        ->get();

    $data['totalCountryPercent'] = 0;

    $data['lastLogins'] = LoginHistory::orderBy('id','desc')->limit(6)->get();

    // =========================
    // Admin Listing Report
    // =========================
    $month = $request->month;
    $from  = $request->from_date;
    $to    = $request->to_date;

$adminListingQuery = Admin::leftJoin('listings', 'admins.id', '=', 'listings.created_by_admin_id')
    ->select(
        'admins.id',
        'admins.first_name',
        'admins.last_name',
        'admins.username',
        'admins.email',

        DB::raw('COUNT(listings.id) as total_listings'),

        DB::raw('COUNT(DISTINCT listings.category_id) as total_categories'),

        DB::raw("SUM(CASE WHEN listings.status = 1 THEN 1 ELSE 0 END) as approved_listings"),

        DB::raw("SUM(CASE WHEN listings.status = 0 THEN 1 ELSE 0 END) as pending_listings"),

        DB::raw("SUM(CASE WHEN listings.status = 2 THEN 1 ELSE 0 END) as rejected_listings")
    );

if (!empty($from) || !empty($to)) {
    if (!empty($from)) {
        $adminListingQuery->whereDate('listings.created_at', '>=', $from);
    }
    if (!empty($to)) {
        $adminListingQuery->whereDate('listings.created_at', '<=', $to);
    }
} elseif (!empty($month)) {
    $adminListingQuery->whereMonth('listings.created_at', Carbon::parse($month)->month)
                      ->whereYear('listings.created_at', Carbon::parse($month)->year);
}

$data['adminListingReport'] = $adminListingQuery
   ->groupBy(
    'admins.id',
    'admins.first_name',
    'admins.last_name',
    'admins.username',
    'admins.email'
)
    ->orderByDesc('total_listings')
    ->get();

    $data['filterMonth'] = $month;
    $data['filterFromDate'] = $from;
    $data['filterToDate'] = $to;

    return view('admin.dashboard', $data);
}

    /**
     * Keeps a long-lived admin form's session alive.
     *
     * The listing form takes real time to fill — address, images, SEO fields,
     * FAQs — and the session expires after config('session.lifetime') minutes
     * of *inactivity*. Typing is not a request, so a form open past that
     * window posted with no session at all: the CSRF check failed, the save
     * was rejected as "Page Expired", and everything typed was lost.
     * storage/logs/admin-errors-* shows exactly that, repeatedly, on
     * POST admin/listing/store with admin_id null.
     *
     * Touching any route inside the `admin` middleware resets the clock, so
     * this does nothing but exist. It also hands back the current CSRF token
     * so the form can refresh its own hidden field.
     */
    public function sessionPing(Request $request)
    {
        return response()->json([
            'ok'    => true,
            'token' => csrf_token(),
        ]);
    }

	public function profile(){
		$admin = auth()->guard('admin')->user();
		return view('admin.profile',compact('admin'));
	}
    public function profileUpdate(Request $request){

		$request->validate([
			'first_name'=>'required',
			'last_name'=>'required',
			'email'=>'required|email',
			'image'=>'nullable|image|mimes:jpg,jpeg,png',
		]);
		$admin = auth()->guard('admin')->user();
		$image = $admin->image;
    	if ($request->hasFile('image')) {
    		try {
	            $imageData = [
	                'file'=>$request->image,
	                'path'=>'assets/central/images/',
	                'size'=>'300x300',
	                'prevFile'=>$admin->image,
	            ];
	            $image = $this->uploadFile('image',$imageData);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
        }

        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->image = $image;
        $admin->save();

        $alert = ['success','Admin profile updated successfully'];
        return back()->withAlert($alert);
	}

	public function profileChange(){
		return view('admin.password');
	}

	public function passwordUpdate(Request $request){
		$request->validate([
			'old_password'=>'required',
			'password'=>'required|confirmed',
		]);

		$admin = auth()->guard('admin')->user();
		if (!Hash::check($request->old_password, $admin->password)) {
			$alert = ['error','Old password doesn\'t match'];
			return back()->withAlert($alert);
		}

		$admin->password      = Hash::make($request->password);
		$admin->remember_token = \Illuminate\Support\Str::random(60);
		$admin->save();

		$this->logoutOtherAdminSessions($admin->id);

		$alert = ['success','Password updated successfully. All other devices have been logged out.'];
		return back()->withAlert($alert);
	}

	private function logoutOtherAdminSessions(int $adminId): void
	{
		try {
			$redis     = \Illuminate\Support\Facades\Redis::connection();
			$prefix    = 'ms_infotech_directory_database_';
			$guardKey  = 'login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d';
			$currentId = session()->getId();
			$needle    = $guardKey . '";i:' . $adminId . ';';

			$deleted = 0;
			$scanned = 0;
			$cursor  = '0';
			do {
				$result = $redis->rawCommand('SCAN', $cursor, 'MATCH', $prefix . '*', 'COUNT', '200');
				$cursor = $result[0];
				foreach ($result[1] as $key) {
					$scanned++;
					if (!str_contains($key, $currentId)) {
						$val = $redis->rawCommand('GET', $key);
						if ($val && str_contains($val, $needle)) {
							$redis->rawCommand('DEL', $key);
							$deleted++;
						}
					}
				}
			} while ($cursor !== '0');

			\Illuminate\Support\Facades\Log::info('logoutOtherAdminSessions', [
				'adminId'   => $adminId,
				'currentId' => $currentId,
				'needle'    => $needle,
				'scanned'   => $scanned,
				'deleted'   => $deleted,
			]);
		} catch (\Throwable $e) {
			\Illuminate\Support\Facades\Log::error('logoutOtherAdminSessions failed', ['error' => $e->getMessage()]);
			report($e);
		}
	}

	public function agents()
{
    $this->authorizeAgentManagement();

    $title = 'Manage Agents';
    // permissions eager-loaded too — the list shows each admin's grant count.
    $agents = Admin::with('adminRole.permissions')->latest()->get();
    return view('admin.agents.index', compact('title', 'agents'));
}

public function agentCreate()
{
    $this->authorizeAgentManagement();

    $title = 'Add Agent';
    $roles = $this->assignableRoles();

    return view('admin.agents.create', compact('title', 'roles'));
}

public function agentStore(Request $request)
{
    $this->authorizeAgentManagement();

    $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name'  => 'required|string|max:100',
        'username'   => 'required|string|max:100|unique:admins,username',
        'email'      => 'required|email|max:150|unique:admins,email',
        'mobile'     => 'nullable|string|max:20|unique:admins,mobile',
        'role_id'    => 'required|exists:roles,id',
        'status'     => 'required|in:0,1',
        'password'   => 'required|min:6|same:password_confirmation',
        'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $role = $this->resolveAssignableRole($request->role_id);

    $image = null;

    if ($request->hasFile('image')) {
        try {
            $imageData = [
                'file'     => $request->image,
                'path'     => 'assets/central/images/',
                'size'     => '300x300',
                'prevFile' => null,
            ];
            $image = $this->uploadFile('image', $imageData);
        } catch (\Exception $e) {
            $alert = ['danger', 'Image upload failed'];
            return back()->withInput()->withAlert($alert);
        }
    }

    Admin::create([
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'username'   => $request->username,
        'email'      => $request->email,
        'mobile'     => $request->mobile,
        'role_id'    => $role->id,
        // Legacy string column, still read by older views/controllers.
        'role'       => $role->slug,
        'status'     => $request->status,
        'password'   => Hash::make($request->password),
        'image'      => $image,
    ]);

    $alert = ['success', 'Agent added successfully'];
    return redirect()->route('admin.agents.index')->withAlert($alert);
}

public function agentEdit($id)
{
    $this->authorizeAgentManagement();

    $title = 'Edit Agent';
    $agent = Admin::findOrFail($id);
    $roles = $this->assignableRoles();

    return view('admin.agents.edit', compact('title', 'agent', 'roles'));
}

public function agentUpdate(Request $request, $id)
{
    $this->authorizeAgentManagement();

    $agent = Admin::findOrFail($id);

    $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name'  => 'required|string|max:100',
        'username'   => 'required|string|max:100|unique:admins,username,' . $agent->id,
        'email'      => 'required|email|max:150|unique:admins,email,' . $agent->id,
        'mobile'     => 'nullable|string|max:20|unique:admins,mobile,' . $agent->id,
        'role_id'    => 'required|exists:roles,id',
        'status'     => 'required|in:0,1',
        'password'   => 'nullable|min:6|same:password_confirmation',
        'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $role = $this->resolveAssignableRole($request->role_id);

    /*
     * Demoting the last super admin would leave nobody able to manage
     * roles or agents again.
     */
    if ($agent->isSuperAdmin() && !$role->isSuperAdmin()) {
        $remainingSuperAdmins = Admin::where('id', '!=', $agent->id)
            ->where(function ($q) {
                $q->where('role', Role::SUPER_ADMIN)
                  ->orWhereHas('adminRole', fn ($r) => $r->where('slug', Role::SUPER_ADMIN));
            })
            ->count();

        if ($remainingSuperAdmins === 0) {
            $alert = ['danger', 'This is the last Super Admin — assign another one before changing this role'];

            return back()->withInput()->withAlert($alert);
        }
    }

    $image = $agent->image;

    if ($request->hasFile('image')) {
        try {
            $imageData = [
                'file'     => $request->image,
                'path'     => 'assets/central/images/',
                'size'     => '300x300',
                'prevFile' => $agent->image,
            ];
            $image = $this->uploadFile('image', $imageData);
        } catch (\Exception $e) {
            $alert = ['danger', 'Image upload failed'];
            return back()->withInput()->withAlert($alert);
        }
    }

    $agent->first_name = $request->first_name;
    $agent->last_name  = $request->last_name;
    $agent->username   = $request->username;
    $agent->email      = $request->email;
    $agent->mobile     = $request->mobile;
    $agent->role_id    = $role->id;
    // Legacy string column, still read by older views/controllers.
    $agent->role       = $role->slug;
    $agent->status     = $request->status;
    $agent->image      = $image;

    if ($request->filled('password')) {
        $agent->password = Hash::make($request->password);
    }

    $agent->save();

    $alert = ['success', 'Agent updated successfully'];
    return redirect()->route('admin.agents.index')->withAlert($alert);
}

public function delete($id)
{
    $this->authorizeAgentManagement();

    try {
        $agent = Admin::findOrFail($id);

        if ($agent->id === auth()->guard('admin')->id()) {
            $alert = ['danger', 'You cannot delete your own account'];
            return back()->withAlert($alert);
        }

        // old image delete
        if (!empty($agent->image)) {
            $imagePath = public_path('assets/central/images/' . $agent->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $agent->delete();

        $alert = ['success', 'Agent deleted successfully'];
        return redirect()->route('admin.agents.index')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}

/**
 * Managing admins used to be hardcoded to the super_admin string. It is now
 * a permission, so a super admin can delegate it — and since super admins
 * implicitly hold every permission, their access is unchanged.
 */
private function authorizeAgentManagement(): void
{
    $admin = auth()->guard('admin')->user();

    abort_unless($admin && $admin->hasPermission('agent.manage'), 403, 'Unauthorized');
}

/**
 * Roles that the current admin is allowed to hand out. Only a super admin
 * can create another super admin — otherwise anyone with agent.manage
 * could quietly promote themselves.
 */
private function assignableRoles()
{
    $admin = auth()->guard('admin')->user();

    return Role::when(!$admin->isSuperAdmin(), function ($q) {
            $q->where('slug', '!=', Role::SUPER_ADMIN);
        })
        ->orderByDesc('is_locked')
        ->orderBy('name')
        ->get();
}

private function resolveAssignableRole($roleId): Role
{
    $role = $this->assignableRoles()->firstWhere('id', (int) $roleId);

    abort_unless($role, 403, 'You cannot assign that role');

    return $role;
}


public function categoryHeadingIndex()
{
    $items = CategoryHeading::with('category')->orderBy('order_by', 'asc')->latest()->get();
    return view('admin.category_heading.index', compact('items'));
}

public function categoryHeadingCreate()
{
    $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();
    return view('admin.category_heading.create', compact('categories'));
}

public function categoryHeadingStore(Request $request)
{
    $request->validate([
        'heading'        => 'required|string|max:255',
        'category_ids'   => 'required|array|min:1|max:4',
        'category_ids.*' => 'exists:categories,id',
        'heading_link'   => 'nullable|string|max:255',
        'order_by'       => 'required|integer|min:0',
    ]);

    CategoryHeading::create([
        'heading'        => $request->heading,
        'category_ids'   => json_encode($request->category_ids), // store as JSON
        'heading_link'   => $request->heading_link,
        'order_by'       => $request->order_by,
        'status'         => $request->has('status') ? 1 : 0,
    ]);

    return redirect()->route('admin.categoryHeading.index')
        ->with('success', 'Category heading added successfully.');
}

public function categoryHeadingEdit($id)
{
    $item = CategoryHeading::findOrFail($id);
    $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();

    return view('admin.category_heading.edit', compact('item', 'categories'));
}

public function categoryHeadingUpdate(Request $request, $id)
{
    $item = CategoryHeading::findOrFail($id);

    $request->validate([
        'heading'        => 'required|string|max:255',
        'category_ids'   => 'required|array|min:1|max:4',
        'category_ids.*' => 'exists:categories,id',
        'heading_link'   => 'nullable|string|max:255',
        'order_by'       => 'required|integer|min:0',
    ]);

    $item->update([
        'heading'      => $request->heading,
        'category_ids' => json_encode($request->category_ids),
        'heading_link' => $request->heading_link,
        'order_by'     => $request->order_by,
        'status'       => $request->has('status') ? 1 : 0,
    ]);

    return redirect()->route('admin.categoryHeading.index')
        ->with('success', 'Category heading updated successfully.');
}

public function categoryHeadingDelete($id)
{
    $item = CategoryHeading::findOrFail($id);
    $item->delete();

    return redirect()->route('admin.categoryHeading.index')
        ->with('success', 'Category heading deleted successfully.');
}

public function categoryHeadingStatus($id)
{
    $item = CategoryHeading::findOrFail($id);
    $item->status = $item->status == 1 ? 0 : 1;
    $item->save();

    return redirect()->route('admin.categoryHeading.index')
        ->with('success', 'Status updated successfully.');
}

public function listingExport(Request $request)
{
    $month = $request->month;
    $from  = $request->from_date;
    $to    = $request->to_date;

    $query = Listing::leftJoin('admins', 'admins.id', '=', 'listings.created_by_admin_id')
        ->leftJoin('categories', 'categories.id', '=', 'listings.category_id')
        ->leftJoin('locations', 'locations.id', '=', 'listings.location_id')
        ->select(
            'listings.name as listing_name',
            'categories.name as category_name',
            'locations.name as location_name',
            'listings.city',
            'listings.area',
            'admins.first_name',
            'admins.last_name',
            'admins.username',
            'admins.email',
            'listings.created_at'
        );

    if (!empty($from) || !empty($to)) {
        if (!empty($from)) {
            $query->whereDate('listings.created_at', '>=', $from);
        }

        if (!empty($to)) {
            $query->whereDate('listings.created_at', '<=', $to);
        }
    } elseif (!empty($month)) {
        $query->whereMonth('listings.created_at', Carbon::parse($month)->month)
              ->whereYear('listings.created_at', Carbon::parse($month)->year);
    }

    $rows = $query->orderByDesc('listings.created_at')->get();

    $fileName = 'admin-listing-report-' . date('Y-m-d') . '.csv';

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=\"$fileName\"",
    ];

    $callback = function () use ($rows) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'Admin Name',
            'Username',
            'Email',
            'Category Name',
            'Listing Name',
            'Location',
            'City',
            'Area',
            'Date',
            'Time',
        ]);

        // Prevent CSV formula injection: listing/category names originate
        // from listing-owner-submitted data, so a value starting with
        // =/+/-/@ is neutralized before being written to the file.
        $safe = function ($value) {
            $value = (string) $value;
            if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
                return "'" . $value;
            }
            return $value;
        };

        foreach ($rows as $row) {
            fputcsv($file, [
                $safe(trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''))),
                $safe($row->username ?? ''),
                $safe($row->email ?? ''),
                $safe($row->category_name ?? 'N/A'),
                $safe($row->listing_name ?? 'N/A'),
                $safe($row->location_name ?? 'N/A'),
                $safe($row->city ?? ''),
                $safe($row->area ?? ''),
                optional($row->created_at)->format('d-m-Y'),
                optional($row->created_at)->format('h:i A'),
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}


public function notificationCounts()
{
    return response()->json([
        'pending_listings' => Listing::where('status', 0)->count(),

        'new_enquiries' => Enquiry::where('status', 0)->count(),

        'guest_chats' => GuestMessage::where('sender_type', 'guest')
            ->whereNull('read_at')
            ->count(),
    ]);
}
}
