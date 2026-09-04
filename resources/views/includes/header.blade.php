@php
$categories = \App\Models\ProductsCategory::doesntHave('parent')->get();

$langs = \App\Models\Lang::all();
@endphp
<header>
    <div class="header__inner container">
        <div class="hamburgerAbsolute">
            <div class="hamburger hamburgerScroll hamburgerMenu">
                <span id="span1"></span>
                <span id="span2"></span>
                <span id="span3"></span>
            </div>
        </div>
        <div class="inner__main">
            <div class="logo__div">
                <a href="{{ route('index') }}" aria-label="home"><img src="{{ asset('client/media/logo.png') }}" alt="logo"></a>
            </div>
            <nav class="header__nav">
                <div class="extraNavigationContainer">
                    <div class="header__langChanger">
                        <span class="langName" style="text-transform: capitalize">Tashkilot</span>
                        <svg class="chevronDown" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                <a class="nav__link {{ request()->is('developments') ? 'activeNav__link' : '' }}" href="{{ route('developments') }}" aria-label="contact">{{ translation('main.developments') }}</a>
                @foreach($categories as $item)
                <a class="nav__link {{ request()->is('subcategories/'.$item->id) ? 'activeNav__link' : '' }}" href="{{ route('subcategories', ['id' => $item->id]) }}" aria-label="a{{ $item->id }}" style="text-transform: capitalize">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                @endforeach
                <a class="nav__link {{ request()->is('contacts') ? 'activeNav__link' : '' }}" href="{{ route('contacts') }}" aria-label="contact">{{ translation('main.contacts') }}</a>
                <!-- <div class="extraNavigationContainer">
                    <div class="extraNavigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="extraNavBox">
                        <div class="clipPathDiv"><span class="clipPath"></span></div>
                        <div class="extraNavContent">
                            <a class="extraNav__link {{ request()->is('about') ? 'blue' : '' }}" href="{{ route('about') }}" aria-label="about">{{ translation('main.about') }}</a>
                            <a class="extraNav__link {{ request()->is('services') ? 'blue' : '' }}" href="{{ route('services') }}" aria-label="services">{{ translation('main.services') }}</a>
                            <a class="extraNav__link {{ request()->is('advantages') ? 'blue' : '' }}" href="{{ route('advantages') }}" aria-label="advantages">{{ translation('main.advantages') }}</a>
                        </div>
                    </div>
                </div> -->
            </nav>
        </div>
        <div class="extraNavigationContainer">
            <div class="header__langChanger">
                <span class="langName" style="text-transform: capitalize">{{ app()->getLocale() }}</span>
                <svg class="chevronDown" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.433 9.4502L12.004 15.8802L5.57397 9.4502" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="extraNavBoxLang">
                <div class="clipPathDiv"><span class="clipPathLang"></span></div>
                <div class="extraNavContentlang">
                    @foreach($langs as $lang)
                    @if($lang->code != app()->getLocale())
                    <a class="extraNav__link" href="{{ route('setlocale', ['lang' => $lang->code]) }}">{{ $lang->title }}</a>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="fixedHeader">
        <div class="header__scroll container">
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
                    @foreach($categories as $item)
                    <a class="nav__link scrollLink {{ request()->is('subcategories/'.$item->id) ? 'activeNav__link' : '' }}" href="{{ route('subcategories', ['id' => $item->id]) }}" aria-label="a{{ $item->id }}" style="text-transform: capitalize">{{ isset($item->title[app()->getLocale()]) ? $item->title[app()->getLocale()] : $item->title[$main_lang->code] }}</a>
                    @endforeach
                    <a class="nav__link scrollLink {{ request()->is('contacts') ? 'activeNav__link' : '' }}" href="{{ route('contacts') }}" aria-label="contact">{{ translation('main.contacts') }}</a>
                    <!-- <div class="extraNavigationContainer">
                        <div class="extraNavigation scrollExtraNav">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div class="extraNavBox">
                            <div class="clipPathDiv"><span class="clipPath"></span></div>
                            <div class="extraNavContent">
                                <a class="extraNav__link {{ request()->is('about') ? 'blue' : '' }}" href="{{ route('about') }}" aria-label="about">{{ translation('main.about') }}</a>
                                <a class="extraNav__link {{ request()->is('services') ? 'blue' : '' }}" href="{{ route('services') }}" aria-label="services">{{ translation('main.services') }}</a>
                                <a class="extraNav__link {{ request()->is('advantages') ? 'blue' : '' }}" href="{{ route('advantages') }}" aria-label="advantages">{{ translation('main.advantages') }}</a>
                            </div>
                        </div>
                    </div> -->
                </nav>
            </div>
            <div class="extraNavigationContainer">
                <div class="header__langChanger">
                    <span class="langName scrollLangName" style="text-transform: capitalize;">{{ app()->getLocale() }}</span>
                    <svg class="chevronDown chevronScroll" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.433 9.4502L12.004 15.8802L5.57397 9.4502" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="extraNavBoxLang">
                    <div class="clipPathDiv"><span class="clipPathLang"></span></div>
                    <div class="extraNavContentlang">
                        @foreach($langs as $lang)
                        @if($lang->code != app()->getLocale())
                        <a class="extraNav__link" href="{{ route('setlocale', ['lang' => $lang->code]) }}">{{ $lang->title }}</a>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>