@extends('marketing.skeleton')

@section('content')
  <body class="marketing register">
    <div class="container">
      <div class="row">
        <div class="col-12 col-md-6 offset-md-3 offset-md-3-right">

          <div class="signup-box">
            <div class="dt w-100">
              <div class="dtc tc">
                <img src="{{ asset('img/monica.svg') }}" width="97" height="88" alt="">
              </div>
            </div>
            <h2>{{ trans('settings.account_link_title') }}</h2>

            @include ('partials.errors')

            <div class="alert alert-info">
              <strong>{{ trans('settings.account_link_invitation_from', ['name' => $accountLink->invitedBy->first_name]) }}</strong>
              <p class="mb-0 mt-2">{{ trans('settings.account_link_invitation_description', ['account' => $accountLink->account->id]) }}</p>
            </div>

            <div class="card">
              <div class="card-body">
                <h5>{{ trans('settings.account_link_what_happens_title') }}</h5>
                <ul class="mb-0">
                  <li>{{ trans('settings.account_link_what_happens_1') }}</li>
                  <li>{{ trans('settings.account_link_what_happens_2') }}</li>
                  <li>{{ trans('settings.account_link_what_happens_3') }}</li>
                </ul>
              </div>
            </div>

            <div class="form-group actions mt-4">
              <form method="POST" action="{{ route('account-links.send', $key) }}" style="display: inline-block; width: 48%; margin-right: 4%;">
                @csrf
                {{-- Security field to verify inviter's email --}}
                <input type="hidden" name="email_security" value="{{ $accountLink->invitedBy->email }}">
                <button type="submit" class="btn btn-primary btn-block">{{ trans('settings.account_link_accept') }}</button>
              </form>
              
              <form method="POST" action="{{ route('account-links.decline', $key) }}" style="display: inline-block; width: 48%;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-secondary btn-block">{{ trans('settings.account_link_decline') }}</button>
              </form>
            </div>

            <div class="form-group links mt-4">
              <ul>
                <li><a href="{{ route('dashboard.index') }}">{{ trans('settings.account_link_back_to_dashboard') }}</a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
    </div>
  </body>
@endsection