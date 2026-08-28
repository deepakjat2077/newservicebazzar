@extends('admin.layouts.master')
@section('content')
@section('title','Push Notification (Firebase) Setting')
@php
    $push = $setting->push_notification ?? null;
    $hasKey = !empty(@$push->service_account);
    $projectId = @$push->project_id;
    $enabled = (bool) @$push->enabled;
@endphp
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card m-b-30">
            <div class="card-header bg-color-primary">
                <h6 class="color-white mb-0">@lang('Push Notification (Firebase) Setting')</h6>
            </div>
            <form action="{{ route('admin.setting.push.update') }}" method="post">
                @csrf
                <div class="card-body">

                    <div class="alert alert-info">
                        @lang('Push sends notifications to the mobile app. It is optional — the in-app notification list works without it. To enable push, paste your Firebase service-account JSON below.')
                        <br>
                        <strong>@lang('Where to get it'):</strong>
                        @lang('Firebase Console → Project settings → Service accounts → Generate new private key → open the downloaded .json file → copy everything → paste here.')
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">@lang('Enable Push')</label>
                        <div class="col-sm-9">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="push_enabled" name="push_enabled" value="1" {{ $enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="push_enabled">@lang('Send push to the app')</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">@lang('Firebase Project')</label>
                        <div class="col-sm-9">
                            <small class="text-muted">
                                @if($projectId)
                                    @lang('Currently saved'): <code>{{ $projectId }}</code>
                                @else
                                    <span class="text-danger">@lang('No credentials set yet')</span>
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">@lang('Service Account JSON')</label>
                        <div class="col-sm-9">
                            <textarea name="service_account_json" class="form-control" rows="10"
                                placeholder='@lang("Paste the whole .json file content here, e.g.") {&#10;  "type": "service_account",&#10;  "project_id": "...",&#10;  "private_key": "...",&#10;  "client_email": "..."&#10;}'></textarea>
                            <small class="text-muted">
                                @if($hasKey)
                                    <span class="text-success">@lang('A key is already saved. Leave this blank to keep it, or paste a new one to replace it.')</span>
                                @else
                                    @lang('Paste the full JSON. It is stored securely and never shown again.')
                                @endif
                            </small>
                        </div>
                    </div>

                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
