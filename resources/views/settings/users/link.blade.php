@extends('layouts.skeleton')

@section('content')
  <div class="settings">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
      <div class="{{ Auth::user()->getFluidLayout() }}">
        <div class="row">
          <div class="col-12">
            <ul class="horizontal">
              <li>
                  <a href="{{ route('dashboard.index') }}">{{ trans('app.breadcrumb_dashboard') }}</a>
                </li>
                <li>
                  <a href="{{ route('settings.index') }}">{{ trans('app.breadcrumb_settings') }}</a>
                </li>
                <li>
                <a href="{{ route('settings.users.index') }}">{{ trans('app.breadcrumb_settings_users') }}</a>
                </li>
                <li>
                  {{ trans('settings.users_link_breadcrumb') }}
                </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="main-content central-form">
      <div class="{{ Auth::user()->getFluidLayout() }}">
        <div class="row">
          <div class="col-12 col-sm-6 offset-sm-3 offset-sm-3-right">
            <div class="br3 ba b--gray-monica bg-white mb4">
              <div class="pa3 bb b--gray-monica">
                <form id="linkForm" method="POST" action="{{ route('settings.users.link.store') }}">
                  @csrf

                  <h2>{{ trans('settings.users_link_title') }}</h2>

                  <p>{{ trans('settings.users_link_description') }}</p>

                  @include('partials.errors')

                  {{-- Email --}}
                  <fieldset class="form-group">
                    <div class="form-group">
                      <label for="email">{{ trans('settings.users_link_email_field') }}</label>
                      <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                  </fieldset>

                  {{-- Explicit confirmation --}}
                  <div class="form-group">
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="confirmation" name="confirmation" value="1" required>
                      <label class="form-check-label" for="confirmation">
                        {{ trans('settings.users_link_confirmation') }}
                      </label>
                    </div>
                  </div>

                  {{-- Submit button inside form --}}
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{ trans('settings.users_link_submit') }}</button>
                    <a href="{{ route('settings.users.index') }}" class="btn btn-secondary">{{ trans('app.cancel') }}</a>
                  </div>

                </form>

              </div>
            </div>

            {{-- Invitations you've sent --}}
            <div class="br3 ba b--gray-monica bg-white mb4">
              <div class="pa3 bb b--gray-monica">
                <h3>{{ trans('settings.users_link_sent_title') }}</h3>
                <p>{{ trans('settings.users_link_sent_description') }}</p>
              </div>
              <div class="pa3">
                @if($sentInvitations->count() > 0)
                  <ul class="list-group">
                    @foreach($sentInvitations as $link)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ $link->user->email }}</strong>
                          <small class="text-muted d-block">{{ trans('settings.users_link_invited_on', ['date' => $link->created_at->format('M j, Y')]) }}</small>
                          <span class="badge badge-warning">{{ trans('settings.users_link_status_pending') }}</span>
                        </div>
                        <form method="POST" action="{{ route('settings.users.link.delete', $link) }}" style="display: inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ trans('settings.users_link_cancel_confirm') }}')">
                            {{ trans('app.cancel') }}
                          </button>
                        </form>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-muted">{{ trans('settings.users_link_no_sent') }}</p>
                @endif
              </div>
            </div>

            {{-- Invitations you've received --}}
            <div class="br3 ba b--gray-monica bg-white mb4">
              <div class="pa3 bb b--gray-monica">
                <h3>{{ trans('settings.users_link_received_title') }}</h3>
                <p>{{ trans('settings.users_link_received_description') }}</p>
              </div>
              <div class="pa3">
                @if($receivedInvitations->count() > 0)
                  <ul class="list-group">
                    @foreach($receivedInvitations as $link)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ trans('settings.account_link_invitation_from', ['name' => $link->invitedBy->first_name]) }}</strong>
                          <small class="text-muted d-block">{{ $link->invitedBy->email }}</small>
                          <small class="text-muted">{{ trans('settings.users_link_invited_on', ['date' => $link->created_at->format('M j, Y')]) }}</small>
                          <span class="badge badge-info">{{ trans('settings.users_link_status_awaiting_response') }}</span>
                        </div>
                        <div>
                          <a href="{{ route('account-links.accept', $link->invitation_key) }}" class="btn btn-sm btn-success mr-1">
                            {{ trans('settings.account_link_accept') }}
                          </a>
                          <form method="POST" action="{{ route('account-links.decline', $link->invitation_key) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ trans('settings.account_link_decline_confirm') }}')">
                              {{ trans('settings.account_link_decline') }}
                            </button>
                          </form>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-muted">{{ trans('settings.users_link_no_received') }}</p>
                @endif
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
@endsection