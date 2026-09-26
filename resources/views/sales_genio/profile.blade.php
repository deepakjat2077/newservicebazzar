<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Profile — Sales Genio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{box-sizing:border-box;}
  button,input,select{font-family:'Poppins',sans-serif;}
  body{margin:0;background:#f1f3f7;font-family:'Poppins',sans-serif;color:#1a2235;}

  .pf-wrap{max-width:900px;margin:0 auto;padding:22px 18px 60px;}
  .pf-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;margin-bottom:18px;overflow:hidden;}
  .pf-head{padding:16px 22px;border-bottom:1px solid #eef0f6;}
  .pf-head h2{margin:0;font-size:15.5px;font-weight:700;color:#12203f;}
  .pf-head p{margin:3px 0 0;font-size:12.5px;color:#8a92a6;}
  .pf-body{padding:20px 22px;}

  /* identity strip */
  .pf-top{display:flex;align-items:center;gap:20px;padding:24px 22px;flex-wrap:wrap;}
  .pf-pic{width:96px;height:96px;border-radius:50%;object-fit:cover;background:#1a73e8;color:#fff;
    display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:600;flex-shrink:0;}
  .pf-who{flex:1;min-width:200px;}
  .pf-who h1{margin:0;font-size:21px;font-weight:700;color:#12203f;}
  .pf-who .role{font-size:13.5px;color:#5b6379;margin-top:3px;}
  .pf-who .code{display:inline-block;background:#eef4fb;color:#1a73e8;border-radius:16px;
    padding:3px 12px;font-size:12px;font-weight:600;margin-top:8px;}
  .pf-pics{display:flex;gap:9px;flex-wrap:wrap;}
  .btn{border-radius:9px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;
    border:1px solid transparent;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
  .btn.blue{background:#1a73e8;color:#fff;}
  .btn.blue:hover{background:#1557b0;}
  .btn.ghost{border-color:#d5dae5;color:#3d4763;background:#fff;}
  .btn.ghost:hover{background:#f5f7fb;}
  .btn.danger{border-color:#f0c6c6;color:#c62828;background:#fff;}

  /* stat row */
  .pf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#eef0f6;}
  .pf-stats div{background:#fff;padding:14px 18px;}
  .pf-stats .k{font-size:11px;color:#8a92a6;text-transform:uppercase;letter-spacing:.4px;}
  .pf-stats .v{font-size:18px;font-weight:700;color:#12203f;margin-top:2px;}

  .row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
  label{display:block;font-size:12px;color:#6b7285;font-weight:600;margin-bottom:6px;}
  input[type=text],input[type=email],input[type=password],select{
    width:100%;border:1px solid #dfe3ec;border-radius:9px;padding:11px 13px;font-size:14px;}
  input:disabled{background:#f6f8fc;color:#8a92a6;}
  .hint{font-size:11.5px;color:#8a92a6;margin-top:5px;}
  .err{font-size:12px;color:#c62828;margin-top:5px;}
  .pf-foot{padding:14px 22px;border-top:1px solid #eef0f6;text-align:right;}

  .flash{border-radius:10px;padding:13px 17px;font-size:14px;margin-bottom:16px;}
  .flash.ok{background:#eafaf1;color:#1f7a45;}
  .flash.bad{background:#fdeaea;color:#c62828;}

  @media (max-width:640px){
    .row{grid-template-columns:1fr;}
    .pf-stats{grid-template-columns:repeat(2,1fr);}
    .pf-top{flex-direction:column;align-items:flex-start;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')

<div class="pf-wrap">

  @if(session('success'))<div class="flash ok"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="flash bad"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif

  {{-- ================= who you are ================= --}}
  <div class="pf-card">
    <div class="pf-top">
      @if($user->avatarUrl())
        <img class="pf-pic" src="{{ $user->avatarUrl() }}" alt="">
      @else
        <div class="pf-pic">{{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}</div>
      @endif

      <div class="pf-who">
        <h1>{{ $user->name }}</h1>
        <div class="role">{{ $user->designation ?: 'Sales Executive' }} @if($user->city) &middot; {{ $user->city }} @endif</div>
        <div class="code">Emp Code: {{ $user->empcode }}</div>
      </div>

      <div class="pf-pics">
        <form action="{{ route('sales_genio.profile.avatar') }}" method="post" enctype="multipart/form-data" id="picForm">
          @csrf
          <input type="file" name="avatar" id="picInput" accept="image/*" hidden
                 onchange="document.getElementById('picForm').submit()">
          <button type="button" class="btn blue" onclick="document.getElementById('picInput').click()">
            <i class="fas fa-camera"></i> {{ $user->avatar ? 'Change picture' : 'Add picture' }}
          </button>
        </form>

        @if($user->avatar)
          <form action="{{ route('sales_genio.profile.avatar.remove') }}" method="post"
                onsubmit="return confirm('Remove your profile picture?')">
            @csrf
            <button type="submit" class="btn danger"><i class="fas fa-trash"></i> Remove</button>
          </form>
        @endif
      </div>
    </div>

    @error('avatar')<div style="padding:0 22px 16px;" class="err">{{ $message }}</div>@enderror

    <div class="pf-stats">
      <div><div class="k">Deal Closed</div><div class="v">{{ $stats['deals'] }}</div></div>
      <div><div class="k">Revenue</div><div class="v">₹{{ number_format($stats['revenue']) }}</div></div>
      <div><div class="k">New Leads</div><div class="v">{{ $stats['new_leads'] }}</div></div>
      <div><div class="k">Follow Ups</div><div class="v">{{ $stats['follow_ups'] }}</div></div>
    </div>
  </div>

  {{-- ================= details ================= --}}
  <form action="{{ route('sales_genio.profile.update') }}" method="post">
    @csrf
    <div class="pf-card">
      <div class="pf-head">
        <h2>My Details</h2>
        <p>These show on your profile and on the businesses you create.</p>
      </div>
      <div class="pf-body">
        <div class="row">
          <div>
            <label>Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div>
            <label>Mobile Number</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="10"
                   oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
            @error('phone')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}">
            @error('email')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div>
            <label>City</label>
            <select name="city">
              <option value="">Select city</option>
              @foreach($cities as $c)
                <option value="{{ $c }}" {{ old('city', $user->city) === $c ? 'selected' : '' }}>{{ $c }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label>Employee Code</label>
            <input type="text" value="{{ $user->empcode }}" disabled>
            <div class="hint">Set by the admin — it cannot be changed here.</div>
          </div>
          <div>
            <label>Designation</label>
            <input type="text" value="{{ $user->designation ?: 'Sales Executive' }}" disabled>
            <div class="hint">Set by the admin.</div>
          </div>
        </div>
      </div>
      <div class="pf-foot">
        <button type="submit" class="btn blue">Save Details</button>
      </div>
    </div>
  </form>

  {{-- ================= password ================= --}}
  <form action="{{ route('sales_genio.profile.password') }}" method="post">
    @csrf
    <div class="pf-card">
      <div class="pf-head">
        <h2>Change Password</h2>
        <p>You will stay signed in on this device after changing it.</p>
      </div>
      <div class="pf-body">
        <div class="row">
          <div style="grid-column:1 / -1;max-width:420px;">
            <label>Current Password</label>
            <input type="password" name="current_password" autocomplete="current-password" required>
            @error('current_password')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div>
            <label>New Password</label>
            <input type="password" name="password" autocomplete="new-password" required>
            <div class="hint">At least 8 characters.</div>
            @error('password')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div>
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
          </div>
        </div>
      </div>
      <div class="pf-foot">
        <button type="submit" class="btn blue">Change Password</button>
      </div>
    </div>
  </form>
</div>

</body>
</html>
