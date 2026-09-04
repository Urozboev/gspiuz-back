<!DOCTYPE html>
<html lang="ru">
@php
$categories = \App\Models\ProductsCategory::doesntHave('parent')
->get();

$langs = \App\Models\Lang::all();

$services = \App\Models\Service::latest()
->get();

@endphp

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Promavto - Bosh sahifa">
    <link rel="shortcut icon" href="{{ asset('client/media/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('client/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/footer.css') }}">
    <script src="{{ asset('client/js/scripts.js') }}" defer></script>

    <title>@yield('title') | {{ $site_info ? $site_info->title[$main_lang->code] : null }}</title>

    @yield('links')
</head>

<body class="body">
    <div class="miniHeader">
        <div class="fixedHeader__inner container">
            <div class="hamburgerAbsolute">
                <div class="hamburger hamburgerScroll hamburgerMenu">
                    <span id="span1"></span>
                    <span id="span2"></span>
                    <span id="span3"></span>
                </div>
            </div>
            <div class="inner__main">
                <div class="logo__div">
                    <a href="{{ route('index') }}" aria-label="home"><img class="newImage" src="{{ asset('client/media/logoNew.png') }}" alt="logo"></a>
                </div>
                <nav class="header__nav">
                    <div class="extraNavigationContainer">
                        <div class="header__langChanger">
                            <span class="langName scrollLangName" style="text-transform: capitalize">Tashkilot</span>
                            <svg class="chevronDown chevronScroll" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.433 9.4502L12.004 15.8802L5.57397 9.4502" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="extraNavBoxLang">
                            <div class="clipPathDiv"><span class="clipPathLang"></span></div>
                            <div class="extraNavContent">
                                <a class="extraNav__link {{ request()->is('about') ? 'blue' : '' }}" href="{{ route('about') }}" aria-label="about">{{ translation('main.about') }}</a>
                                <a class="extraNav__link {{ request()->is('services') ? 'blue' : '' }}" href="{{ route('services') }}" aria-label="services">{{ translation('main.services') }}</a>
                                <a class="extraNav__link {{ request()->is('advantages') ? 'blue' : '' }}" href="{{ route('advantages') }}" aria-label="advantages">{{ translation('main.advantages') }}</a>
                            </div>
                        </div>
                    </div>
                    <a class="nav__link scrollLink {{ request()->is('developments') ? 'activeNav__link' : '' }}" href="{{ route('developments') }}" aria-label="contact">{{ translation('main.developments') }}</a>
                    <a class="nav__link scrollLink {{ request()->is('services') ? 'activeNav__link' : '' }}" href="{{ route('services') }}" aria-label="services">{{ translation('main.services') }}</a>
                    @foreach($categories as $item)
                    <a class="nav__link scrollLink {{ request()->is('subcategories/'.$item->id) ? 'blue' : '' }}" href="{{ route('subcategories', ['id' => $item->id]) }}" aria-label="a{{ $item->id }}" style="text-transform: capitalize">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                    @endforeach
                    <a class="nav__link scrollLink {{ request()->is('contacts') ? 'activeNav__link' : '' }}" href="{{ route('contacts') }}" aria-label="contact">{{ translation('main.contacts') }}</a>
                    <!-- <div class="extraNavigation scrollExtraNav">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div> -->
                </nav>
            </div>
            <div class="extraNavigationContainer">
                <div class="header__langChanger">
                    <span class="langName scrollLangName" style=" text-transform: capitalize">{{ app()->getLocale() }}</span>
                    <div class="chevronParent">
                        <svg class="chevronDown chevronScroll" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.433 9.4502L12.004 15.8802L5.57397 9.4502" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="extraNavBoxLang">
                    <div class="clipPathDiv"><span class="clipPathLang"></span></div>
                    <div class="extraNavContentlang">
                        @foreach($langs as $lang)
                        @if($lang->code != app()->getLocale())
                        <a class="extraNav__link" href="{{ route('setlocale', ['lang' => $lang->code]) }}" style="text-transform: capitalize">{{ $lang->title }}</a>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="mobileHeader hidden">
            <div class="mobileHeader__inner">
                <nav class="mobileNav">
                    <a class="mobileNav__link {{ request()->is('services') ? 'activeMobileLink' : '' }}" href="{{ route('services') }}" aria-label="">{{ translation('main.services') }}</a>
                    @foreach($categories as $item)
                    <a class="mobileNav__link {{ request()->is('subcategories/'.$item->id) ? 'blue' : '' }}" href="{{ route('subcategories', ['id' => $item->id]) }}" aria-label="a{{ $item->id }}" style="text-transform: capitalize">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                    @endforeach
                    <a class="mobileNav__link {{ request()->is('developments') ? 'activeMobileLink' : '' }}" href="{{ route('developments') }}" aria-label="">{{ translation('main.developments') }}</a>
                    <a class="mobileNav__link {{ request()->is('contacts') ? 'activeMobileLink' : '' }}" href="{{ route('contacts') }}" aria-label="">{{ translation('main.contacts') }}</a>
                    <a class="mobileNav__link {{ request()->is('about') ? 'activeMobileLink' : '' }}" href="{{ route('about') }}" aria-label="">{{ translation('main.about') }}</a>
                    <a class="mobileNav__link {{ request()->is('advantages') ? 'activeMobileLink' : '' }}" href="{{ route('advantages') }}" aria-label="">{{ translation('main.advantages') }}</a>
                </nav>
                <div class="mobileContent">
                    <div class="mobilePhoneNumber">
                        <div class="flexContacts">
                            <svg class="miniSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.4406 13.0004C19.2206 13.0004 18.9906 12.9304 18.7706 12.8804C18.3251 12.7822 17.8873 12.6518 17.4606 12.4904C16.9967 12.3216 16.4867 12.3304 16.0289 12.515C15.5711 12.6996 15.1977 13.047 14.9806 13.4904L14.7606 13.9404C13.7866 13.3985 12.8916 12.7256 12.1006 11.9404C11.3154 11.1494 10.6424 10.2544 10.1006 9.28036L10.5206 9.00036C10.964 8.78327 11.3114 8.40989 11.496 7.95205C11.6806 7.49421 11.6894 6.98427 11.5206 6.52036C11.3618 6.09278 11.2315 5.65515 11.1306 5.21036C11.0806 4.99036 11.0406 4.76036 11.0106 4.53036C10.8892 3.82598 10.5202 3.1881 9.97021 2.7316C9.42021 2.27509 8.72529 2.02997 8.01059 2.04036H5.01059C4.57962 2.03631 4.15284 2.12517 3.7593 2.30089C3.36576 2.4766 3.0147 2.73505 2.73002 3.05864C2.44534 3.38222 2.23372 3.76335 2.10958 4.17607C1.98543 4.58879 1.95167 5.02342 2.01059 5.45036C2.54333 9.63974 4.45662 13.5322 7.44824 16.513C10.4399 19.4938 14.3393 21.3929 18.5306 21.9104H18.9106C19.648 21.9114 20.36 21.6409 20.9106 21.1504C21.227 20.8674 21.4797 20.5205 21.6521 20.1327C21.8244 19.7448 21.9126 19.3248 21.9106 18.9004V15.9004C21.8983 15.2057 21.6454 14.5369 21.1949 14.008C20.7445 13.4791 20.1244 13.123 19.4406 13.0004ZM19.9406 19.0004C19.9404 19.1423 19.91 19.2827 19.8514 19.412C19.7927 19.5413 19.7073 19.6566 19.6006 19.7504C19.4892 19.8474 19.3586 19.9198 19.2173 19.9629C19.076 20.0059 18.9272 20.0187 18.7806 20.0004C15.0355 19.5202 11.5568 17.8068 8.89331 15.1306C6.22978 12.4545 4.533 8.96769 4.07059 5.22036C4.05467 5.07387 4.06862 4.92569 4.11159 4.78475C4.15456 4.64381 4.22566 4.51304 4.32059 4.40036C4.4143 4.29369 4.52965 4.2082 4.65897 4.14957C4.78829 4.09095 4.9286 4.06054 5.07059 4.06036H8.07059C8.30314 4.05518 8.53021 4.13124 8.71273 4.27543C8.89525 4.41962 9.0218 4.62293 9.07059 4.85036C9.11059 5.12369 9.16059 5.39369 9.22059 5.66036C9.33611 6.1875 9.48985 6.70554 9.68059 7.21036L8.28059 7.86036C8.16089 7.91528 8.05321 7.9933 7.96375 8.08995C7.87428 8.1866 7.80479 8.29997 7.75926 8.42355C7.71373 8.54713 7.69306 8.67849 7.69844 8.81008C7.70381 8.94167 7.73513 9.0709 7.79059 9.19036C9.22979 12.2731 11.7078 14.7512 14.7906 16.1904C15.0341 16.2904 15.3071 16.2904 15.5506 16.1904C15.6753 16.1457 15.7899 16.0768 15.8878 15.9875C15.9856 15.8983 16.0648 15.7905 16.1206 15.6704L16.7406 14.2704C17.2576 14.4552 17.7852 14.6088 18.3206 14.7304C18.5873 14.7904 18.8573 14.8404 19.1306 14.8804C19.358 14.9291 19.5613 15.0557 19.7055 15.2382C19.8497 15.4207 19.9258 15.6478 19.9206 15.8804L19.9406 19.0004Z" fill="currentColor" />
                            </svg>
                            <div class="miniPhoneNumbers">
                                @php
                                if(isset($site_info->phone_number)) {
                                $phones = explode('|', $site_info->phone_number);
                                }
                                @endphp
                                @if(isset($phones))
                                <a href="tel:{{ $phones[0] }}" aria-label="phoneNumber">{{ $phones[0] }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="flexContacts">
                            <svg class="miniSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="mailto:{{ $site_info->email ?? null }}" aria-label="email">{{ $site_info->email ?? null }}</a>
                        </div>
                    </div>
                    <div class="mobileSocialMedia">
                        @if(isset($site_info->telegram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->telegram }}" aria-label="telegram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                                    <path d="M248,8C111.033,8,0,119.033,0,256S111.033,504,248,504,496,392.967,496,256,384.967,8,248,8ZM362.952,176.66c-3.732,39.215-19.881,134.378-28.1,178.3-3.476,18.584-10.322,24.816-16.948,25.425-14.4,1.326-25.338-9.517-39.287-18.661-21.827-14.308-34.158-23.215-55.346-37.177-24.485-16.135-8.612-25,5.342-39.5,3.652-3.793,67.107-61.51,68.335-66.746.153-.655.3-3.1-1.154-4.384s-3.59-.849-5.135-.5q-3.283.746-104.608,69.142-14.845,10.194-26.894,9.934c-8.855-.191-25.888-5.006-38.551-9.123-15.531-5.048-27.875-7.717-26.8-16.291q.84-6.7,18.45-13.7,108.446-47.248,144.628-62.3c68.872-28.647,83.183-33.623,92.511-33.789,2.052-.034,6.639.474,9.61,2.885a10.452,10.452,0,0,1,3.53,6.716A43.765,43.765,0,0,1,362.952,176.66Z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->instagram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->instagram }}" aria-label="instagram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->youtube))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->youtube }}" aria-label="youtube">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                    <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                    <p class="copyTextMobile">&copy; <span class="currentYearSpan"></span> Promavto</p>
                </div>
            </div>
        </div>
    </div>

    @yield('content')

    <footer>
        <div class="footer__inner container">
            <div class="inner__top">
                <div class="footer__mainDiv">
                    <div class="logo__div">
                        <a href="./index.html" aria-label="home"><img src="{{ asset('client/media/logo.png') }}" alt="logo"></a>
                    </div>
                    <p class="motto">{{ translation('main.footer_about') }}</p>
                    <div id="large" class="socialMediaDiv">
                        @if(isset($site_info->telegram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->telegram }}" aria-label="telegram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                                    <path d="M248,8C111.033,8,0,119.033,0,256S111.033,504,248,504,496,392.967,496,256,384.967,8,248,8ZM362.952,176.66c-3.732,39.215-19.881,134.378-28.1,178.3-3.476,18.584-10.322,24.816-16.948,25.425-14.4,1.326-25.338-9.517-39.287-18.661-21.827-14.308-34.158-23.215-55.346-37.177-24.485-16.135-8.612-25,5.342-39.5,3.652-3.793,67.107-61.51,68.335-66.746.153-.655.3-3.1-1.154-4.384s-3.59-.849-5.135-.5q-3.283.746-104.608,69.142-14.845,10.194-26.894,9.934c-8.855-.191-25.888-5.006-38.551-9.123-15.531-5.048-27.875-7.717-26.8-16.291q.84-6.7,18.45-13.7,108.446-47.248,144.628-62.3c68.872-28.647,83.183-33.623,92.511-33.789,2.052-.034,6.639.474,9.61,2.885a10.452,10.452,0,0,1,3.53,6.716A43.765,43.765,0,0,1,362.952,176.66Z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->instagram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->instagram }}" aria-label="instagram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->youtube))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->youtube }}" aria-label="youtube">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                    <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="footer__pageNav">
                    <p class="navTitle">Menyu</p>
                    <nav class="pageNav">
                        @foreach($categories as $item)
                        <a href="{{ route('subcategories', ['id' => $item->id]) }}" class="ftNavLink" aria-label="">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                        @endforeach
                        <a href="{{ route('advantages') }}" class="ftNavLink" aria-label="">{{ translation('main.advantages') }}</a>
                        <a href="{{ route('about') }}" class="ftNavLink" aria-label="">{{ translation('main.about') }}</a>
                    </nav>
                </div>
                <div class="footer__serviceNav">
                    <p class="navTitle">Xizmatlar</p>
                    <nav class="serviceNav">
                        @foreach($services as $item)
                        <a href="{{ route('services') }}#service{{ $item->id }}" class="ftNavLink" aria-label="">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                        @endforeach
                    </nav>
                </div>
                <div class="footer__contactNav">
                    <p class="navTitle">Aloqa</p>
                    <nav class="contactNav">
                        <div class="contactContent">
                            <svg class="footerSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.4406 13.0004C19.2206 13.0004 18.9906 12.9304 18.7706 12.8804C18.3251 12.7822 17.8873 12.6518 17.4606 12.4904C16.9967 12.3216 16.4867 12.3304 16.0289 12.515C15.5711 12.6996 15.1977 13.047 14.9806 13.4904L14.7606 13.9404C13.7866 13.3985 12.8916 12.7256 12.1006 11.9404C11.3154 11.1494 10.6424 10.2544 10.1006 9.28036L10.5206 9.00036C10.964 8.78327 11.3114 8.40989 11.496 7.95205C11.6806 7.49421 11.6894 6.98427 11.5206 6.52036C11.3618 6.09278 11.2315 5.65515 11.1306 5.21036C11.0806 4.99036 11.0406 4.76036 11.0106 4.53036C10.8892 3.82598 10.5202 3.1881 9.97021 2.7316C9.42021 2.27509 8.72529 2.02997 8.01059 2.04036H5.01059C4.57962 2.03631 4.15284 2.12517 3.7593 2.30089C3.36576 2.4766 3.0147 2.73505 2.73002 3.05864C2.44534 3.38222 2.23372 3.76335 2.10958 4.17607C1.98543 4.58879 1.95167 5.02342 2.01059 5.45036C2.54333 9.63974 4.45662 13.5322 7.44824 16.513C10.4399 19.4938 14.3393 21.3929 18.5306 21.9104H18.9106C19.648 21.9114 20.36 21.6409 20.9106 21.1504C21.227 20.8674 21.4797 20.5205 21.6521 20.1327C21.8244 19.7448 21.9126 19.3248 21.9106 18.9004V15.9004C21.8983 15.2057 21.6454 14.5369 21.1949 14.008C20.7445 13.4791 20.1244 13.123 19.4406 13.0004ZM19.9406 19.0004C19.9404 19.1423 19.91 19.2827 19.8514 19.412C19.7927 19.5413 19.7073 19.6566 19.6006 19.7504C19.4892 19.8474 19.3586 19.9198 19.2173 19.9629C19.076 20.0059 18.9272 20.0187 18.7806 20.0004C15.0355 19.5202 11.5568 17.8068 8.89331 15.1306C6.22978 12.4545 4.533 8.96769 4.07059 5.22036C4.05467 5.07387 4.06862 4.92569 4.11159 4.78475C4.15456 4.64381 4.22566 4.51304 4.32059 4.40036C4.4143 4.29369 4.52965 4.2082 4.65897 4.14957C4.78829 4.09095 4.9286 4.06054 5.07059 4.06036H8.07059C8.30314 4.05518 8.53021 4.13124 8.71273 4.27543C8.89525 4.41962 9.0218 4.62293 9.07059 4.85036C9.11059 5.12369 9.16059 5.39369 9.22059 5.66036C9.33611 6.1875 9.48985 6.70554 9.68059 7.21036L8.28059 7.86036C8.16089 7.91528 8.05321 7.9933 7.96375 8.08995C7.87428 8.1866 7.80479 8.29997 7.75926 8.42355C7.71373 8.54713 7.69306 8.67849 7.69844 8.81008C7.70381 8.94167 7.73513 9.0709 7.79059 9.19036C9.22979 12.2731 11.7078 14.7512 14.7906 16.1904C15.0341 16.2904 15.3071 16.2904 15.5506 16.1904C15.6753 16.1457 15.7899 16.0768 15.8878 15.9875C15.9856 15.8983 16.0648 15.7905 16.1206 15.6704L16.7406 14.2704C17.2576 14.4552 17.7852 14.6088 18.3206 14.7304C18.5873 14.7904 18.8573 14.8404 19.1306 14.8804C19.358 14.9291 19.5613 15.0557 19.7055 15.2382C19.8497 15.4207 19.9258 15.6478 19.9206 15.8804L19.9406 19.0004Z" fill="currentColor" />
                            </svg>
                            @php
                            if(isset($site_info->phone_number)) {
                            $phones = explode('|', $site_info->phone_number);
                            }
                            @endphp
                            @if(isset($phones))
                            <a href="tel:{{ $phones[0] }}" class="ftNavLink">{{ $phones[0] }}</a>
                            @endif
                        </div>
                        <div class="contactContent">
                            <svg class="footerSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="mailto:{{ $site_info->email ?? null }}" class="ftNavLink">{{ $site_info->email ?? null }}</a>
                        </div>
                        <div class="contactContent">
                            <svg class="footerSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="ftNavLink locationText">{{ isset($site_info->address[app()->getLocale()]) ? $site_info->address[app()->getLocale()] : (isset($site_info->address[$main_lang->code]) ? $site_info->address[$main_lang->code] : null ) }}</p>
                        </div>
                    </nav>
                    <div id="mini" class="socialMediaDiv">
                        @if(isset($site_info->telegram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->telegram }}" aria-label="telegram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                                    <path d="M248,8C111.033,8,0,119.033,0,256S111.033,504,248,504,496,392.967,496,256,384.967,8,248,8ZM362.952,176.66c-3.732,39.215-19.881,134.378-28.1,178.3-3.476,18.584-10.322,24.816-16.948,25.425-14.4,1.326-25.338-9.517-39.287-18.661-21.827-14.308-34.158-23.215-55.346-37.177-24.485-16.135-8.612-25,5.342-39.5,3.652-3.793,67.107-61.51,68.335-66.746.153-.655.3-3.1-1.154-4.384s-3.59-.849-5.135-.5q-3.283.746-104.608,69.142-14.845,10.194-26.894,9.934c-8.855-.191-25.888-5.006-38.551-9.123-15.531-5.048-27.875-7.717-26.8-16.291q.84-6.7,18.45-13.7,108.446-47.248,144.628-62.3c68.872-28.647,83.183-33.623,92.511-33.789,2.052-.034,6.639.474,9.61,2.885a10.452,10.452,0,0,1,3.53,6.716A43.765,43.765,0,0,1,362.952,176.66Z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->instagram))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->instagram }}" aria-label="instagram">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                        @if(isset($site_info->youtube))
                        <div>
                            <a class="socialMedia" href="{{ $site_info->youtube }}" aria-label="youtube">
                                <svg class="sm__logo" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                    <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="innner__bottom">
                <div class="copyTextDiv">
                    <p class="copyText">&copy; <span class="currentYearSpan"></span> Promavto</p>
                    <p class="copyText">{{ translation('main.privacyPolicy') }}</p>
                </div>
                <div class="ndcDiv">
                    <div class="ndcLogoDiv">
                        <svg class="ndcLogo" viewBox="0 0 79 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_940_562)">
                                <path d="M13.6637 7.8797V0H11.4446V7.8797L7.48944 1.05263L5.57226 2.16541L9.52741 8.99248L2.67383 5.05263L1.5718 6.9624L8.4103 10.9023H0.5V13.1128H8.4103L1.5718 17.0526L2.67383 18.9624L9.52741 15.0226L5.57226 21.8496L7.48944 22.9474L11.4446 16.1203V24H13.6637V16.1203" fill="white" />
                                <path d="M11.4446 7.8797V0H13.6637V7.8797L17.6189 1.06767L19.5361 2.16541L15.5809 8.99248L22.4345 5.05263L23.5365 6.9624L16.6829 10.9023H24.5932V13.1128H16.6829L23.5365 17.0526L22.4345 18.9624L15.5809 15.0226L19.5361 21.8496L17.6189 22.9474L13.6637 16.1203V24H11.4446V16.1203" fill="white" />
                                <path d="M32.5859 6.33066V7.67487H36.1393H39.1995C39.5053 7.67487 39.7932 7.81872 39.9769 8.0632C40.2362 8.40442 40.3443 8.83154 40.3574 9.2599C40.3833 10.1101 40.4256 11.6948 40.4407 13.4251L40.4781 18.0925L41.8471 18.1149L43.2086 18.1299V15.0531C43.2011 11.4088 43.1039 8.65316 42.9543 7.96612C42.8121 7.30895 42.6027 6.91315 42.1314 6.42027C41.1681 5.37718 39.6294 5.13754 38.2111 5.07018C37.4081 5.03203 36.4245 4.99957 35.3389 4.99391L32.5859 4.98644V6.33066Z" fill="white" />
                                <path d="M47.4072 9.437C47.4072 11.9014 47.4072 14.7588 47.4072 14.7588V18.1381C47.4072 18.1381 48.3124 18.1232 49.0006 18.1381L50.1751 18.1445L53.7135 18.1072C57.5661 18.0699 57.9252 18.0325 58.6209 17.6367C59.1146 17.3455 59.8402 16.6211 60.0796 16.188C60.4387 15.5382 60.4985 14.9632 60.5509 12.1553C60.6033 8.83957 60.5359 8.01064 60.1544 7.23398C59.6158 6.14367 58.4937 5.28487 57.4165 5.12804C57.1546 5.0907 54.7982 5.03843 52.1799 5.01603L47.4072 4.96375V9.437ZM56.2121 7.7418C56.2121 7.7418 57.016 7.7418 57.429 8.06917C57.9105 8.45076 57.9177 9.14097 57.9177 9.75528V11.5579C57.9177 14.5002 57.9027 14.657 57.5212 15.008C57.1098 15.3889 57.035 15.3964 53.4517 15.4262L50.1003 15.4486V11.6102C50.1003 9.50421 50.1003 7.7418 50.1003 7.7418C50.1676 7.66712 56.2121 7.7418 56.2121 7.7418Z" fill="white" />
                                <path d="M68.7204 5.06112C67.5833 5.12086 66.8951 5.25528 66.3341 5.5316C65.8628 5.7631 65.2119 6.42027 64.8454 7.0401C64.2918 7.97359 64.422 8.07067 64.4145 12.0436L64.4146 18.1299H65.6201H66.8502H72.1242H77.3981V16.7857V15.4415H72.2064H67.0223L66.9849 12.6186C66.9699 11.0728 66.9774 9.56424 66.9998 9.273C67.1046 8.18269 67.4562 7.87651 68.7578 7.74955C69.1693 7.71221 71.2788 7.67487 73.4557 7.67487H77.3981V6.33066V4.98644L73.6053 5.00138C71.5107 5.00138 69.3189 5.03125 68.7204 5.06112Z" fill="white" />
                                <path d="M30.262 4.98748H30.043V12.9472V18.1299H30.4394C30.6639 18.1299 31.2549 18.1075 31.7486 18.0776L32.6612 18.0328V12.9397V4.98748H32.3719H30.262Z" fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_940_562">
                                    <rect width="78" height="24" fill="white" transform="translate(0.5)" />
                                </clipPath>
                            </defs>
                        </svg>
                        <p class="currentYearSpan miniYear"></p>
                    </div>
                    <div class="adTextDiv">
                        <p class="adText">{{ translation('main.ad') }} <span class="adText">National Development Community</span></p>
                        <p class="adText">{{ translation('main.siteinfo') }}</p>
                    </div>
                </div>
            </div>
            <div class="toTopBtn">
                <svg class="footerSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 19V5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 12L12 5L19 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
    </footer>

    @yield('scripts')

</body>

</html>