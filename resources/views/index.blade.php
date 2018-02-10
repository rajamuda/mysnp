@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'githubAuth' => config('services.github.client_id'),
    'defaultParams' => config('app.defaultParams'),
];

$polyfills = [
    'Promise',
    'Object.assign',
    'Object.values',
    'Array.prototype.find',
    'Array.prototype.findIndex',
    'Array.prototype.includes',
    'String.prototype.includes',
    'String.prototype.startsWith',
    'String.prototype.endsWith',
];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>{{ config('app.name') }}</title>
  <style>
    @font-face{
      font-family: Icons;
      src: {{ asset('fonts/icons.eot') }};
      src: url("{{ asset('fonts/icons.eot?#iefix') }}") format('embedded-opentype'), url("{{ asset('fonts/icons.woff2') }}") format('woff2'), url("{{ asset('fonts/icons.woff') }}") format('woff'), url("{{ asset('fonts/icons.ttf') }}") format('truetype'), url("{{ asset('fonts/icons.svg#icons') }}") format('svg');
      font-style: normal;
      font-weight: normal;
      font-variant: normal;
      text-decoration: inherit;
      text-transform: none;
    }

    /* Sticky footer styles
    -------------------------------------------------- */
    html {
      position: relative;
      min-height: 100%;
    }
    body {
      /* Margin bottom by footer height */
      margin-bottom: 40px;
    }
    .footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      /* Set the fixed height of the footer here */
      height: 40px;
      line-height: 40px; /* Vertically center the text there */
      background-color: #fbfbfb;
      border-top: 1px solid #e8eced;
      box-shadow: 2px 0px 2px 2px rgba(0, 0, 0, 0.1);
    }


    /* Custom page CSS
    -------------------------------------------------- */
    /* Not required for template or sticky footer method. */

    body > .container {
      padding: 60px 15px 0;
    }

    .footer > .container {
      padding-right: 15px;
      padding-left: 15px;
    }
  </style>
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/icon.min.css') }}">
</head>
<body>
  <div id="app"></div>

  {{-- Global configuration object --}}
  <script>window.config = @json($config);</script>

  {{-- Polyfill JS features via polyfill.io --}}
  {{-- <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features={{ implode(',', $polyfills) }}"></script> --}}
  <script src="{{ asset('js/polyfill.js') }}"></script>

  {{-- Load the application scripts --}}
  @if (app()->isLocal())
    <script src="{{ mix('js/app.js') }}"></script>
  @else
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
  @endif
</body>
</html>
