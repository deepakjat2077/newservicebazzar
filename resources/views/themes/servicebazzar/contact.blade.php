@extends($themeView.'layouts.site')
@section('content')
@section('title','Contacts')


<style>
	.page-header {
   background-size: cover;
    height: 250px;
    display: inline-block;
    background-repeat: no-repeat;
    width: 100%;
}

.page-header .overlay {
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-header h2 {
    color: #fff;
    font-size: 30px;
    font-weight: 700;
    margin: 0;
}
.sks{
	align-items: center;
    justify-content: center;
    display: contents;

}
</style>
<section class="page-header"
    style="background: url('{{ asset('assets/images/calling.png') }}') center center/cover no-repeat;">
    <div class="overlay">
        <div class=" sks container">
            <h2>Contact Us</h2>
        </div>
    </div>
</section>
<section class="contact-section pt-80 pb-80">
	<div class="container">
		<div class="contact-info">
			<div class="row mb__30 justify-content-center">
				@foreach($contacts as $contact)
				<div class="col-lg-4 col-md-6">
					<div class="contact-element">
						<div class="icon-area">
							@php echo $contact->content->icon @endphp
						</div>
						<div class="content-area">
							<h5>{{ __($contact->content->title) }}</h5>
							<p>{{ __($contact->content->content) }}</p>
						</div>
					</div>
				</div>
				@endforeach
			</div>
		</div>
		<div class="contact-area">
			<form action="{{ route('contact.sumbit') }}" method="post" class="captchaVerify">
				@csrf
				@include('themes.servicebazzar.partials.form-guard')
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>@lang('Name')</label>
							<input type="text" name="name" class="form-control">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>@lang('Email')</label>
							<input type="email" name="email" class="form-control">
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>@lang('Subject')</label>
							<input type="text" name="subject" class="form-control">
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>@lang('Message')</label>
							<textarea class="form-control" name="message" rows="5"></textarea>
						</div>
					</div>
                    @if(@$setting->recaptcha->status == 1)
                    <div class="col-md-12">
                        <div class="form-group">
                            @include('includes.recaptcha')
                        </div>
                    </div>
                    @endif
					<div class="col-md-12">
						<div class="form-group">
							<button class="btn btn__base btn-block">@lang('Submit')</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

@endsection
