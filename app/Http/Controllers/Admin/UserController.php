<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Http\Libs\SendMail;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function all(){
    	$title = 'All Users';
    	$users = User::orderBy('id','desc')->paginate(20);
    	return view('admin.users.list',compact('users','title'));
    }

    public function unverified(){
    	$title = 'Un-verified Users';
    	$users = User::orderBy('id','desc')->where('email_verified',0)->paginate(20);
    	return view('admin.users.list',compact('users','title'));
    }

    public function banned(){
    	$title = 'Banned Users';
    	$users = User::orderBy('id','desc')->where('status',0)->paginate(20);
    	return view('admin.users.list',compact('users','title'));
    }

    public function details($id){
        /*
         * The stat tiles on this page ran ten separate listings queries
         * (four counts, four order-by-first, two package remainders). One
         * grouped count query and one ordered fetch of id/name/view/review
         * cover all of them.
         */
    	$user = User::with('package')->findOrfail($id);

        $listingCounts = $user->listings()
            ->selectRaw('status, is_featured, count(*) as total')
            ->groupBy('status', 'is_featured')
            ->get();

        $listingStats = [
            'total'    => (int) $listingCounts->sum('total'),
            'featured' => (int) $listingCounts->where('is_featured', 1)->sum('total'),
            'pending'  => (int) $listingCounts->where('status', 0)->sum('total'),
            'approved' => (int) $listingCounts->where('status', 1)->sum('total'),
            'rejected' => (int) $listingCounts->where('status', 2)->sum('total'),
        ];

        $listingNames = $user->listings()
            ->select('id', 'name', 'view', 'review')
            ->get();

        $listingStats['most_viewed']   = optional($listingNames->sortByDesc('view')->first())->name;
        $listingStats['least_viewed']  = optional($listingNames->sortBy('view')->first())->name;
        $listingStats['most_reviewed'] = optional($listingNames->sortByDesc('review')->first())->name;
        $listingStats['least_reviewed'] = optional($listingNames->sortBy('review')->first())->name;

        $transactions = $user->transactions;

    	return view('admin.users.details',compact('user','transactions','listingStats'));
    }

    public function update(Request $request,$id){
        $request->validate([
            'first_name'=>'required',
            'last_name'=>'required',
        ]);
        $user = User::findOrfail($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->status = $request->status ? 1 : 0;
        $user->email_verified = $request->email_verified ? 1 : 0;
        $user->verified_tag = $request->verified_tag ? 1 : 0;
        $user->save();
        $alert = ['success','User details updated successfully'];
        return back()->withAlert($alert);
    }

    public function updateBalance(Request $request,$id,Booster $booster, SendMail $mail){
        $request->validate([
            'type'=>'required|integer|in:1,2',
            'type'=>'required|numeric|gt:0',
        ]);
        $user = User::findOrfail($id);
        if ($request->type == 1) {
            $type = '+';
            $remark = 'balance_add';
            $details = 'Balance add by admin';
            $template = 'balance_add';
        }else{
            $type = '-';
            $remark = 'balance_cut';
            $details = 'Balance cut by admin';
            $template = 'balance_cut';
        }
        $booster->userBalance($user,$request->amount,$type);
        $trx = trxNumber();
        $booster->storeTrx([
            'user'=>$user,
            'trx_number'=>$trx,
            'amount'=>$request->amount,
            'charge'=>0,
            'after_charge'=>$request->amount,
            'type'=>$type,
            'remark'=>$remark,
            'details'=>$details,
        ]);
        $setting = Setting::first();
        $data = [
            'name'=>$user->fullname(),
            'email'=>$user->email,
            'template'=>$template,
            'value'=>[
                "amount"=>numberFormat($request->amount),
                "site_currency"=>$setting->site_currency,
                "trx"=>$trx,
                "current_balance"=>numberFormat($user->balance),
            ]
        ];

        $mail->sendMail($data);

        $alert = ['success','User balance updated successfully'];
        return back()->withAlert($alert);

    }

    public function sendMail($id){
        $user = User::findOrfail($id);
        return view('admin.users.mail',compact('user'));
    }

    public function submitMail(Request $request,$id,SendMail $mail){
        $request->validate([
            'subject'=>'required',
            'message'=>'required',
        ]);
        $user = User::findOrfail($id);
        $data = [
            'name'=>$user->fullname(),
            'email'=>$user->email,
            'template'=>'user_mail',
            'subject'=>$request->subject,
            'value'=>[
                'message'=>$request->message
            ]
        ];
        $mail->sendMail($data);
        $alert = ['success','Mail sent successfully'];
        return back()->withAlert($alert);
    }

    public function deposit($id){
        $user = User::findOrfail($id);
        $title = 'Deposits of '.$user->username;
        $deposits = Payment::where('user_id',$user->id)->where('status',1)->orderBy('id','desc')->paginate(20);
        return view('admin.deposit.index',compact('deposits','title'));
    }

    public function login($id)
    {
        // Impersonation is a high-privilege action (full access to any
        // user's account) — restrict it to super admins and leave an audit
        // trail of who impersonated whom.
        $admin = auth()->guard('admin')->user();
        abort_unless($admin && $admin->role === 'super_admin', 403);

        $user = User::findOrFail($id);

        LoginHistory::create([
            'user_id'      => $user->id,
            'browser'      => 'Admin Impersonation',
            'os'           => 'By admin #' . $admin->id . ' (' . $admin->username . ')',
            'ip'           => request()->ip(),
            'country'      => null,
            'country_code' => null,
        ]);

        auth()->login($user);
        return redirect()->route('user.dashboard');
    }

     public function delete($id)
{
    try {
        $user = User::findOrFail($id);

        // old image delete
        if (!empty($user->image)) {
            $imagePath = public_path('assets/images/user/' . $user->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $user->delete();

        $alert = ['success', 'User deleted successfully'];
        return redirect()->route('admin.users.all')->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }

    
}

public function showRegistrationForm(){
        return view('admin.users.add');
    }

     protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:30'],
            'last_name'  => ['required', 'string', 'max:30'],
            'username'   => ['required', 'string', 'min:6', 'unique:users,username'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile'     => ['required', 'string', 'max:255', 'unique:users,mobile'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function register(Request $request, SendMail $mail)
    {
        $this->validator($request->all())->validate();

        $otp = verification_code(6);

        session([
            'register_data' => [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'username'   => $request->username,
                'mobile'     => $request->mobile,
                'email'      => $request->email,
                'password'   => $request->password,
            ],
            'register_otp' => $otp,
            'register_otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        try {
            $data = [
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'template' => 'verification_code',
                'value' => [
                    'code' => $otp,
                ]
            ];

            $mail->sendMail($data);
        } catch (\Exception $e) {
            return back()->withAlert(['danger', $e->getMessage()]);
        }

        return redirect()->route('admin.users.otp.form')->withAlert(['success', 'OTP sent to your email']);
    }
       public function showOtpForm()
    {
        if (!session()->has('register_data')) {
            return redirect()->route('admin.users.register')->withAlert(['danger', 'Please register first']);
        }

        $email = session('register_data.email');
        return view('admin.users.verify_otp', compact('email'));
    }

    public function verifyOtp(Request $request)
{
    $request->validate([
        'otp' => ['required', 'digits:6'],
    ]);

    if (!session()->has('register_data') || !session()->has('register_otp')) {
        return redirect()->route('admin.users.register')
            ->withAlert(['danger', 'Session expired. Please register again']);
    }

    if (now()->timestamp > session('register_otp_expires_at')) {
        session()->forget(['register_data', 'register_otp', 'register_otp_expires_at']);
        return redirect()->route('admin.users.register')
            ->withAlert(['danger', 'OTP expired. Please register again']);
    }

    if (!hash_equals((string) session('register_otp'), (string) $request->otp)) {
        return back()->withAlert(['danger', 'Invalid OTP']);
    }

    $data = session('register_data');

    $user = User::create([
        'first_name'           => $data['first_name'],
        'last_name'            => $data['last_name'],
        'username'             => $data['username'],
        'mobile'               => $data['mobile'],
        'email'                => $data['email'],
        'email_verified'       => 1,
        'email_otp'            => null,
        'email_otp_expires_at' => null,
        'password'             => Hash::make($data['password']),
    ]);

    $booster = new Booster();
    $booster->createLoginLog($user);

    session()->forget(['register_data', 'register_otp', 'register_otp_expires_at']);

    return redirect()->route('admin.users.all')
        ->withAlert(['success', 'User registered successfully.']);
}   
  public function resendOtp(SendMail $mail)
    {
        if (!session()->has('register_data')) {
            return redirect()->route('admin.users.register')->withAlert(['danger', 'Please register first']);
        }

        $otp = verification_code(6);

        session([
            'register_otp' => $otp,
            'register_otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $data = session('register_data');

        try {
            $mailData = [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'template' => 'verification_code',
                'value' => [
                    'code' => $otp,
                ]
            ];

            $mail->sendMail($mailData);
        } catch (\Exception $e) {
            return back()->withAlert(['danger', $e->getMessage()]);
        }

        return back()->withAlert(['success', 'OTP resent successfully']);
    }

    public function quickStore(Request $request)
{
    try {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:6|max:50',
            'mobile'     => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:1000',
        ]);

        $baseUsername = \Illuminate\Support\Str::slug($request->first_name . ' ' . $request->last_name, '');
        if (!$baseUsername) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $i = 1;

        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $baseUsername . $i;
            $i++;
        }

        $user = \App\Models\User::create([
            'first_name'     => trim($request->first_name),
            'last_name'      => trim($request->last_name ?? ''),
            'username'       => $username,
            'email'          => trim($request->email),
            'mobile'         => $request->mobile,
            'address'        => $request->address,
            'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
            'status'         => 1,
            'email_verified' => 1,
            'verified_tag'   => 1,
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'id'       => $user->id,
                'name'     => trim($user->first_name . ' ' . $user->last_name),
                'username' => $user->username,
                'email'    => $user->email,
                'mobile'   => $user->mobile,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
