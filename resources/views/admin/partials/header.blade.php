 <header class="relative z-10 bg-white border-b border-gray border-solid py-5 px-8 pr-8">
                <div class="flex justify-between">
                    <div class="flex items-center space-x-6 lg:space-x-0">
                        <button type="button" class="block lg:hidden text-2xl text-black" x-on:click="sideMenu = ! sideMenu">
                            <svg width="20" height="12" viewBox="0 0 20 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M1 6H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M1 11H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="w-[30%] hidden md:block">
                            <form action="#">
                                <div class="w-[250px] relative">
                                    <input class="input h-12 w-full pr-[45px]" type="text" placeholder="Search Here...">
                                    <button class="absolute top-1/2 right-6 translate-y-[-50%] hover:text-theme">
                                        <svg class="-translate-y-[2px]" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                         </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                   
                    <div class="flex items-center justify-end space-x-6">
                        <div class="md:hidden">
                            <button class="relative w-[40px] h-[40px] leading-[40px] rounded-md text-textBody border border-gray hover:bg-themeLight hover:text-theme hover:border-themeLight" x-on:click="searchOverlay = ! searchOverlay">
                                <svg class="-translate-y-[2px]" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                 </svg>
                            </button>
                        </div>
                        <div class="relative" x-data="{ notificationTable: false }">
                            <button x-on:click="notificationTable = ! notificationTable" class="relative w-[40px] h-[40px] leading-[40px] rounded-md text-gray border border-gray hover:bg-themeLight hover:text-theme hover:border-themeLight">
                                <svg class="-translate-y-[1px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                <g>
                                    <path stroke="currentColor" d="M23.259,16.2l-2.6-9.371A9.321,9.321,0,0,0,2.576,7.3L.565,16.35A3,3,0,0,0,3.493,20H7.1a5,5,0,0,0,9.8,0h3.47a3,3,0,0,0,2.89-3.8ZM12,22a3,3,0,0,1-2.816-2h5.632A3,3,0,0,1,12,22Zm9.165-4.395a.993.993,0,0,1-.8.395H3.493a1,1,0,0,1-.976-1.217l2.011-9.05a7.321,7.321,0,0,1,14.2-.372l2.6,9.371A.993.993,0,0,1,21.165,17.605Z"/></g>
                                </svg>
                                <span class="w-[20px] h-[20px] inline-block bg-danger rounded-full absolute -top-[4px] -right-[4px] border-[2px] border-white text-xs leading-[18px] font-medium text-white">05</span>
                            </button>
                            <div
                                x-show="notificationTable"
                                x-on:click.outside="notificationTable = false"
                                x-transition:enter="transition ease-out duration-200 origin-top"
                                x-transition:enter-start="opacity-0 scale-y-90"
                                x-transition:enter-end="opacity-100 scale-y-100"
                                x-transition:leave="transition ease-in duration-200 origin-top"
                                x-transition:leave-start="opacity-100 scale-y-200"
                                x-transition:leave-end="opacity-0 scale-y-90"
                                class="absolute w-[280px] sm:w-[350px] h-[285px] overflow-y-scroll overflow-item top-full -right-[60px] sm:right-0 shadow-lg rounded-md bg-white py-5 px-5"
                            >

                            <div class="flex items-center justify-between last:border-0 border-b border-gray pb-4 mb-4 last:pb-0 last:mb-0">
                                <div class="flex items-center space-x-3">
                                    <div class="">
                                        <img class="w-[40px] h-[40px] rounded-md" src="{{asset ('assets/admin/img/product/prodcut-1.jpg')}}" alt="img">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-0 leading-none">Green shirt for women</h5>
                                        <span class="text-tiny leading-none">Jan 21, 2023 08:30 AM</span>
                                    </div>
                                </div>
                                <div class="">
                                    <button class="hover:text-danger">
                                        <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between last:border-0 border-b border-gray pb-4 mb-4 last:pb-0 last:mb-0">
                                <div class="flex items-center space-x-3">
                                    <div class="">
                                        <img class="w-[40px] h-[40px] rounded-md" src="{{asset ('assets/admin/img/product/prodcut-2.jpg')}}" alt="img">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-0 leading-none">Red School Bag</h5>
                                        <span class="text-tiny leading-none">Jan 25, 2023 10:05 PM</span>
                                    </div>
                                </div>
                                <div class="">
                                    <button class="hover:text-danger">
                                        <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between last:border-0 border-b border-gray pb-4 mb-4 last:pb-0 last:mb-0">
                                <div class="flex items-center space-x-3">
                                    <div class="">
                                        <img class="w-[40px] h-[40px] rounded-md" src="{{asset ('assets/admin/img/product/prodcut-3.jpg')}}" alt="img">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-0 leading-none">Shoe for men</h5>
                                        <span class="text-tiny leading-none">Feb 20, 2023 05:00 PM</span>
                                    </div>
                                </div>
                                <div class="">
                                    <button class="hover:text-danger">
                                        <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between last:border-0 border-b border-gray pb-4 mb-4 last:pb-0 last:mb-0">
                                <div class="flex items-center space-x-3">
                                    <div class="">
                                        <img class="w-[40px] h-[40px] rounded-md" src="{{asset ('assets/admin/img/product/prodcut-4.jpg')}}" alt="img">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-0 leading-none">Yellow Bag for women</h5>
                                        <span class="text-tiny leading-none">Feb 10, 2023 11:15 AM</span>
                                    </div>
                                </div>
                                <div class="">
                                    <button class="hover:text-danger">
                                        <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between last:border-0 border-b border-gray pb-4 mb-4 last:pb-0 last:mb-0">
                                <div class="flex items-center space-x-3">
                                    <div class="">
                                        <img class="w-[40px] h-[40px] rounded-md" src="{{asset('assets/admin/img/product/prodcut-5.jpg')}}" alt="img">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-0 leading-none">Blavk Bag for women</h5>
                                        <span class="text-tiny leading-none">Feb 15, 2023 01:20 PM</span>
                                    </div>
                                </div>
                                <div class="">
                                    <button class="hover:text-danger">
                                        <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                                        <path fill="currentColor" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                    </button>
                                </div>
                            </div>

                            </div>
                        </div>
                        <div class="relative w-[70%] flex justify-end items-center" x-data="{ userOption: false }">
                            <button class="relative" type="button" x-on:click="userOption = ! userOption">
                                <img class="w-[40px] h-[40px] rounded-md" src="{{asset('assets/admin/img/users/user-10.jpg')}}" alt="">
                                <span class="w-[12px] h-[12px] inline-block bg-green-500 rounded-full absolute -top-[4px] -right-[4px] border-[2px] border-white"></span>
                            </button>
                            <div 
                                x-show="userOption"
                                x-on:click.outside="userOption = false"
                                x-transition:enter="transition ease-out duration-200 origin-top"
                                x-transition:enter-start="opacity-0 scale-y-90"
                                x-transition:enter-end="opacity-100 scale-y-100"
                                x-transition:leave="transition ease-in duration-200 origin-top"
                                x-transition:leave-start="opacity-100 scale-y-200"
                                x-transition:leave-end="opacity-0 scale-y-90"
                                class="absolute w-[280px] top-full right-0 shadow-lg rounded-md bg-white py-5 px-5"
    
                            >
                                <div class="flex items-center space-x-3 border-b border-gray pb-3 mb-2">
                                    <div class="">
                                        <img class="w-[50px] h-[50px] rounded-md" src="{{asset('assets/admin/img/users/user-10.jpg')}}" alt="">
                                    </div>
                                    <div class="">
                                        <h5 class="text-base mb-1 leading-none">Shahnewaz</h5>
                                        <p class="mb-0 text-tiny leading-none">shahnewaz@mail.com</p>
                                    </div>
                                </div>
                                <ul>
                                    <li>
                                        <a href="index.html" class="px-5 py-2 w-full block hover:bg-gray rounded-md hover:text-theme text-base">Dashboard</a>
                                    </li>
                                    <li>
                                        <a href="profile.html" class="px-5 py-2 w-full block hover:bg-gray rounded-md hover:text-theme text-base">
                                            Account Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a href="login.html" class="px-5 py-2 w-full block hover:bg-gray rounded-md hover:text-theme text-base">
                                            Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- search -->
                <div class="fixed top-0 left-0 w-full bg-white p-10 z-50 transition-transform duration-300 md:hidden" :class="searchOverlay ? 'translate-y-[0px]' : ' -translate-y-[230px] lg:translate-y-[0]'">
                    <form action="#">
                        <div class="relative mb-3">
                            <input class="input h-12 w-full pr-[45px]" type="text" placeholder="Search Here...">
                            <button class="absolute top-1/2 right-6 translate-y-[-50%] hover:text-theme">
                                <svg class="-translate-y-[2px]" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                 </svg>
                            </button>
                        </div>
                    </form>
                    <div class="">
                        <span class="text-tiny mr-2">Keywords :</span>
                        <a href="#" class="inline-block px-3 py-1 border border-gray6 text-tiny leading-none rounded-[4px] hover:text-white hover:bg-theme hover:border-theme">Customer</a>
                        <a href="#" class="inline-block px-3 py-1 border border-gray6 text-tiny leading-none rounded-[4px] hover:text-white hover:bg-theme hover:border-theme">Product</a>
                        <a href="#" class="inline-block px-3 py-1 border border-gray6 text-tiny leading-none rounded-[4px] hover:text-white hover:bg-theme hover:border-theme">Orders</a>
                    </div>
                </div>
                <div 
                    class="fixed top-0 left-0 w-full h-full z-40 bg-black/70 transition-all duration-300" 
                    :class="searchOverlay ? 'visible opacity-1' : '  invisible opacity-0 '"
                    x-on:click="searchOverlay = ! searchOverlay"
                >
                </div>
            </header>