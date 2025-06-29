@extends('admin.layouts.app')

@section('content')
<div class="body-content px-8 py-8 bg-slate-100">
    <div class="flex justify-between items-end flex-wrap">
        <div class="page-title mb-7">
            <h3 class="mb-0 text-4xl">Dashboard</h3>
            <p class="text-textBody m-0">Welcome to your dashboard</p>
        </div>
        <div class=" mb-7">
            <a href="add-product.html" class="tp-btn px-5 py-2">Add Product</a>
        </div>
    </div>

    <!-- card -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <div class="widget-item bg-white p-6 flex justify-between rounded-md">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">356</h4>
                <p class="text-tiny leading-4">Orders Received</p>
                <div class="badge space-x-1"> <span>10%</span>
                    <svg width="12" height="12" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1V18C1 19.66 2.34 21 4 21H21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4 16L8.59 10.64C9.35 9.76001 10.7 9.7 11.52 10.53L12.47 11.48C13.29 12.3 14.64 12.25 15.4 11.37L20 6" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <div>
                <span class="text-lg text-white rounded-full flex items-center justify-center h-12 w-12 shrink-0 bg-success">
                    <svg width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.37 7.87988H16.62" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.38 7.87988L6.13 8.62988L8.38 6.37988" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.37 14.8799H16.62" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.38 14.8799L6.13 15.6299L8.38 13.3799" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 21H14C19 21 21 19 21 14V8C21 3 19 1 14 1H8C3 1 1 3 1 8V14C1 19 3 21 8 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">$5680</h4>
                <p class="text-tiny leading-4">Average Daily Sales</p>
                <div class="badge space-x-1 text-purple bg-purple/10"><span>30%</span>
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 512.001 512.001" width="12" height="12">
                        <path fill="currentColor" d="M506.35,80.699c-7.57-7.589-19.834-7.609-27.43-0.052L331.662,227.31l-42.557-42.557c-7.577-7.57-19.846-7.577-27.423,0 L89.076,357.36c-7.577,7.57-7.577,19.853,0,27.423c3.782,3.788,8.747,5.682,13.712,5.682c4.958,0,9.93-1.894,13.711-5.682 l158.895-158.888l42.531,42.524c7.57,7.57,19.808,7.577,27.397,0.032l160.97-160.323 C513.881,100.571,513.907,88.288,506.35,80.699z" />
                        <path fill="currentColor" d="M491.96,449.94H38.788V42.667c0-10.712-8.682-19.394-19.394-19.394S0,31.955,0,42.667v426.667 c0,10.712,8.682,19.394,19.394,19.394H491.96c10.712,0,19.394-8.682,19.394-19.394C511.354,458.622,502.672,449.94,491.96,449.94z" />
                        <path fill="currentColor" d="M492.606,74.344H347.152c-10.712,0-19.394,8.682-19.394,19.394s8.682,19.394,19.394,19.394h126.061v126.067 c0,10.705,8.682,19.394,19.394,19.394S512,249.904,512,239.192V93.738C512,83.026,503.318,74.344,492.606,74.344z" />
                    </svg>
                </div>
            </div>
            <div>
                <span class="text-lg text-white rounded-full flex items-center justify-center h-12 w-12 shrink-0 bg-purple">
                    <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 21H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M3.59998 7.37988H2C1.45 7.37988 1 7.82988 1 8.37988V16.9999C1 17.5499 1.45 17.9999 2 17.9999H3.59998C4.14998 17.9999 4.59998 17.5499 4.59998 16.9999V8.37988C4.59998 7.82988 4.14998 7.37988 3.59998 7.37988Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.7999 4.18994H9.19995C8.64995 4.18994 8.19995 4.63994 8.19995 5.18994V16.9999C8.19995 17.5499 8.64995 17.9999 9.19995 17.9999H10.7999C11.3499 17.9999 11.7999 17.5499 11.7999 16.9999V5.18994C11.7999 4.63994 11.3499 4.18994 10.7999 4.18994Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M17.9999 1H16.3999C15.8499 1 15.3999 1.45 15.3999 2V17C15.3999 17.55 15.8499 18 16.3999 18H17.9999C18.5499 18 18.9999 17.55 18.9999 17V2C18.9999 1.45 18.5499 1 17.9999 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">5.8K</h4>
                <p class="text-tiny leading-4">New Customers This Month</p>
                <div class="badge space-x-1 text-info bg-info/10"><span>13%</span>
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 512.001 512.001" width="12" height="12">
                        <path fill="currentColor" d="M506.35,80.699c-7.57-7.589-19.834-7.609-27.43-0.052L331.662,227.31l-42.557-42.557c-7.577-7.57-19.846-7.577-27.423,0 L89.076,357.36c-7.577,7.57-7.577,19.853,0,27.423c3.782,3.788,8.747,5.682,13.712,5.682c4.958,0,9.93-1.894,13.711-5.682 l158.895-158.888l42.531,42.524c7.57,7.57,19.808,7.577,27.397,0.032l160.97-160.323 C513.881,100.571,513.907,88.288,506.35,80.699z" />
                        <path fill="currentColor" d="M491.96,449.94H38.788V42.667c0-10.712-8.682-19.394-19.394-19.394S0,31.955,0,42.667v426.667 c0,10.712,8.682,19.394,19.394,19.394H491.96c10.712,0,19.394-8.682,19.394-19.394C511.354,458.622,502.672,449.94,491.96,449.94z" />
                        <path fill="currentColor" d="M492.606,74.344H347.152c-10.712,0-19.394,8.682-19.394,19.394s8.682,19.394,19.394,19.394h126.061v126.067 c0,10.705,8.682,19.394,19.394,19.394S512,249.904,512,239.192V93.738C512,83.026,503.318,74.344,492.606,74.344z" />
                    </svg>
                </div>
            </div>
            <div>
                <span class="text-lg text-white rounded-full flex items-center justify-center h-12 w-12 shrink-0 bg-info">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 6.16C16.94 6.15 16.87 6.15 16.81 6.16C15.43 6.11 14.33 4.98 14.33 3.58C14.33 2.15 15.48 1 16.91 1C18.34 1 19.49 2.16 19.49 3.58C19.48 4.98 18.38 6.11 17 6.16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.9699 13.44C17.3399 13.67 18.8499 13.43 19.9099 12.72C21.3199 11.78 21.3199 10.24 19.9099 9.30004C18.8399 8.59004 17.3099 8.35003 15.9399 8.59003" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4.96998 6.16C5.02998 6.15 5.09998 6.15 5.15998 6.16C6.53998 6.11 7.63998 4.98 7.63998 3.58C7.63998 2.15 6.48998 1 5.05998 1C3.62998 1 2.47998 2.16 2.47998 3.58C2.48998 4.98 3.58998 6.11 4.96998 6.16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.99994 13.44C4.62994 13.67 3.11994 13.43 2.05994 12.72C0.649941 11.78 0.649941 10.24 2.05994 9.30004C3.12994 8.59004 4.65994 8.35003 6.02994 8.59003" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11 13.63C10.94 13.62 10.87 13.62 10.81 13.63C9.42996 13.58 8.32996 12.45 8.32996 11.05C8.32996 9.61997 9.47995 8.46997 10.91 8.46997C12.34 8.46997 13.49 9.62997 13.49 11.05C13.48 12.45 12.38 13.59 11 13.63Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8.08997 16.78C6.67997 17.72 6.67997 19.26 8.08997 20.2C9.68997 21.27 12.31 21.27 13.91 20.2C15.32 19.26 15.32 17.72 13.91 16.78C12.32 15.72 9.68997 15.72 8.08997 16.78Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">580</h4>
                <p class="text-tiny leading-4">Pending Orders</p>
                <div class="badge space-x-1 text-warning bg-warning/10"><span>10%</span>
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 512.001 512.001" width="12" height="12">
                        <path fill="currentColor" d="M506.35,80.699c-7.57-7.589-19.834-7.609-27.43-0.052L331.662,227.31l-42.557-42.557c-7.577-7.57-19.846-7.577-27.423,0 L89.076,357.36c-7.577,7.57-7.577,19.853,0,27.423c3.782,3.788,8.747,5.682,13.712,5.682c4.958,0,9.93-1.894,13.711-5.682 l158.895-158.888l42.531,42.524c7.57,7.57,19.808,7.577,27.397,0.032l160.97-160.323 C513.881,100.571,513.907,88.288,506.35,80.699z" />
                        <path fill="currentColor" d="M491.96,449.94H38.788V42.667c0-10.712-8.682-19.394-19.394-19.394S0,31.955,0,42.667v426.667 c0,10.712,8.682,19.394,19.394,19.394H491.96c10.712,0,19.394-8.682,19.394-19.394C511.354,458.622,502.672,449.94,491.96,449.94z" />
                        <path fill="currentColor" d="M492.606,74.344H347.152c-10.712,0-19.394,8.682-19.394,19.394s8.682,19.394,19.394,19.394h126.061v126.067 c0,10.705,8.682,19.394,19.394,19.394S512,249.904,512,239.192V93.738C512,83.026,503.318,74.344,492.606,74.344z" />
                    </svg>
                </div>
            </div>
            <div>
                <span class="text-lg text-white rounded-full flex items-center justify-center h-12 w-12 shrink-0 bg-warning">
                    <svg width="23" height="22" viewBox="0 0 23 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.17004 6.43994L11 11.5499L19.77 6.46991" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11 20.6099V11.5399" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M20.61 8.17V13.83C20.61 13.88 20.61 13.92 20.6 13.97C19.9 13.36 19 13 18 13C17.06 13 16.19 13.33 15.5 13.88C14.58 14.61 14 15.74 14 17C14 17.75 14.21 18.46 14.58 19.06C14.67 19.22 14.78 19.37 14.9 19.51L13.07 20.52C11.93 21.16 10.07 21.16 8.92999 20.52L3.59 17.56C2.38 16.89 1.39001 15.21 1.39001 13.83V8.17C1.39001 6.79 2.38 5.11002 3.59 4.44002L8.92999 1.48C10.07 0.84 11.93 0.84 13.07 1.48L18.41 4.44002C19.62 5.11002 20.61 6.79 20.61 8.17Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22 17C22 18.2 21.47 19.27 20.64 20C19.93 20.62 19.01 21 18 21C15.79 21 14 19.21 14 17C14 15.74 14.58 14.61 15.5 13.88C16.19 13.33 17.06 13 18 13C20.21 13 22 14.79 22 17Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.25 15.75V17.25L17 18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <!-- card -->
    <div class="grid-cols-4 gap-6 hidden">

        <div class="card-item bg-white py-7 px-7 flex items-start rounded-md mb-6">
            <div class="card-icon">
                <span class="inline-block w-12 h-12 mr-5 rounded-full bg-green-200 text-green-600 leading-[3rem] text-center text-xl">
                    <svg height="16" viewBox="0 0 512 512" width="16" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <path fill="currentColor" d="m256 4.8c-138.3 0-251 112.5-251 251s112.5 251 251 251 251-112.5 251-251-112.7-251-251-251zm84.1 343c-5.4 10.2-12.8 18.7-21.7 25.5-9 6.6-19 11.5-30.2 14.9-3.1.9-6.3 1.8-9.5 2.3v15.1c0 12.6-10.2 22.8-22.8 22.8s-23-10.2-23-23v-13.8c-7-1.1-14-2.5-20.7-4.5-13.3-4-24.8-10.6-35.2-18-4.5-3.1-10.4-10.1-10.4-10.1-2.3-2.3-5.4-6.1-5.4-14.2 0-11.7 9.5-21.4 21-21.2 7.7 0 15.1 6.3 15.1 6.3 7.5 6.1 11.7 9.9 19.2 13.1 11 4.9 23.7 7.2 37.7 7.2 8.6 0 16.7-1.6 24.3-4.7 7.2-3.1 13.1-7.4 17.8-12.9 4.3-5.4 6.5-11.9 6.5-19.8 0-10.2-3.8-19-11.3-27.1-7.7-8.1-20.8-13.1-39.2-15.3-17.6-1.8-33.4-6.3-46.7-13.3-13.5-7-24.3-16.4-31.6-27.7-7.4-11.3-11.1-24.4-11.1-38.8 0-15.6 4.5-29.3 13.1-40.6 8.6-11 20.1-19.6 34.5-25.3 7.2-2.9 14.6-5.2 22.5-6.6v-15.3c0-12.6 10.2-22.8 22.8-22.8s23 10.2 23 23v14.7c5.7.9 11 2.3 15.8 4.1 10.8 4 20.1 9.7 27.8 16.9 4.3 4.1 8.3 8.6 11.9 13.5.5.7 1.3 1.4 1.8 2.2 2.3 3.2 3.6 7.4 3.6 11.9 0 11.7-11.7 20.1-21.2 21-9 .9-17.4-9.3-17.4-9.3-3.8-4.9-8.1-9-13.1-12.4-7.5-5-18.1-7.7-31.6-7.9-14.4-.2-26.2 2.7-35.6 8.8-8.6 5.6-12.8 13.5-12.8 24.6 0 5.7 1.3 11.5 4 16.9 2.5 5 7.5 9.7 14.9 13.8 7.7 4.3 19.6 7.4 35 9.2 25.9 2.5 46.7 10.8 62.3 24.6 16 14 24.1 33.1 24.1 56.6-.1 13.5-2.8 25.4-8.2 35.6z" />
                        </g>
                    </svg>
                </span>
            </div>
            <div class="card-content">
                <h6 class=" text-heading mb-2">Revenue</h6>
                <h4 class="text-3xl mb-0 text-textBody">$10,000</h4>
                <p class="text-tiny leading-normal mb-0 font-medium">Shipping fees are not included </p>
            </div>
        </div>
        <div class="card-item bg-white py-7 px-7 flex items-start rounded-md mb-6">
            <div class="card-icon">
                <span class="inline-block w-12 h-12 mr-5 rounded-full bg-orange-200 text-orange-600 leading-[3rem] text-center text-xl">
                    <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 469.333 469.333">
                        <g>
                            <g>
                                <path fill="currentColor" d="M405.333,149.333h-64V64H42.667C19.093,64,0,83.093,0,106.667v234.667h42.667c0,35.307,28.693,64,64,64s64-28.693,64-64
                                              h128c0,35.307,28.693,64,64,64c35.307,0,64-28.693,64-64h42.667V234.667L405.333,149.333z M106.667,373.333
                                              c-17.707,0-32-14.293-32-32s14.293-32,32-32s32,14.293,32,32S124.373,373.333,106.667,373.333z M362.667,373.333
                                              c-17.707,0-32-14.293-32-32s14.293-32,32-32s32,14.293,32,32S380.373,373.333,362.667,373.333z M341.333,234.667v-53.333h53.333
                                              l41.92,53.333H341.333z" />
                            </g>
                        </g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                    </svg>
                </span>
            </div>
            <div class="card-content">

                <h6 class=" text-heading mb-2">Orders</h6>
                <h4 class="text-3xl mb-0 text-textBody">3240</h4>
                <p class="text-tiny leading-normal mb-0 font-medium"> Excluding orders in transit </p>
            </div>
        </div>
        <div class="card-item bg-white py-7 px-7 flex items-start rounded-md mb-6">
            <div class="card-icon">
                <span class="inline-block w-12 h-12 mr-5 rounded-full bg-blue-200 text-blue-600 leading-[3rem] text-center text-xl">
                    <svg height="16" viewBox="0 0 512 512" width="16" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <path fill="currentColor" d="m256 4.8c-138.3 0-251 112.5-251 251s112.5 251 251 251 251-112.5 251-251-112.7-251-251-251zm84.1 343c-5.4 10.2-12.8 18.7-21.7 25.5-9 6.6-19 11.5-30.2 14.9-3.1.9-6.3 1.8-9.5 2.3v15.1c0 12.6-10.2 22.8-22.8 22.8s-23-10.2-23-23v-13.8c-7-1.1-14-2.5-20.7-4.5-13.3-4-24.8-10.6-35.2-18-4.5-3.1-10.4-10.1-10.4-10.1-2.3-2.3-5.4-6.1-5.4-14.2 0-11.7 9.5-21.4 21-21.2 7.7 0 15.1 6.3 15.1 6.3 7.5 6.1 11.7 9.9 19.2 13.1 11 4.9 23.7 7.2 37.7 7.2 8.6 0 16.7-1.6 24.3-4.7 7.2-3.1 13.1-7.4 17.8-12.9 4.3-5.4 6.5-11.9 6.5-19.8 0-10.2-3.8-19-11.3-27.1-7.7-8.1-20.8-13.1-39.2-15.3-17.6-1.8-33.4-6.3-46.7-13.3-13.5-7-24.3-16.4-31.6-27.7-7.4-11.3-11.1-24.4-11.1-38.8 0-15.6 4.5-29.3 13.1-40.6 8.6-11 20.1-19.6 34.5-25.3 7.2-2.9 14.6-5.2 22.5-6.6v-15.3c0-12.6 10.2-22.8 22.8-22.8s23 10.2 23 23v14.7c5.7.9 11 2.3 15.8 4.1 10.8 4 20.1 9.7 27.8 16.9 4.3 4.1 8.3 8.6 11.9 13.5.5.7 1.3 1.4 1.8 2.2 2.3 3.2 3.6 7.4 3.6 11.9 0 11.7-11.7 20.1-21.2 21-9 .9-17.4-9.3-17.4-9.3-3.8-4.9-8.1-9-13.1-12.4-7.5-5-18.1-7.7-31.6-7.9-14.4-.2-26.2 2.7-35.6 8.8-8.6 5.6-12.8 13.5-12.8 24.6 0 5.7 1.3 11.5 4 16.9 2.5 5 7.5 9.7 14.9 13.8 7.7 4.3 19.6 7.4 35 9.2 25.9 2.5 46.7 10.8 62.3 24.6 16 14 24.1 33.1 24.1 56.6-.1 13.5-2.8 25.4-8.2 35.6z" />
                        </g>
                    </svg>
                </span>
            </div>
            <div class="card-content">
                <h6 class=" text-heading mb-2">Products</h6>
                <h4 class="text-3xl mb-0 text-textBody">1456</h4>
                <p class="text-tiny leading-normal mb-0 font-medium">in 19 Categories </p>
            </div>
        </div>
        <div class="card-item bg-white py-7 px-7 flex items-start rounded-md mb-6">
            <div class="card-icon">
                <span class="inline-block w-12 h-12 mr-5 rounded-full bg-teal-200 text-teal-600 leading-[3rem] text-center text-xl">
                    <svg height="16" viewBox="0 0 512 512" width="16" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <path fill="currentColor" d="m256 4.8c-138.3 0-251 112.5-251 251s112.5 251 251 251 251-112.5 251-251-112.7-251-251-251zm84.1 343c-5.4 10.2-12.8 18.7-21.7 25.5-9 6.6-19 11.5-30.2 14.9-3.1.9-6.3 1.8-9.5 2.3v15.1c0 12.6-10.2 22.8-22.8 22.8s-23-10.2-23-23v-13.8c-7-1.1-14-2.5-20.7-4.5-13.3-4-24.8-10.6-35.2-18-4.5-3.1-10.4-10.1-10.4-10.1-2.3-2.3-5.4-6.1-5.4-14.2 0-11.7 9.5-21.4 21-21.2 7.7 0 15.1 6.3 15.1 6.3 7.5 6.1 11.7 9.9 19.2 13.1 11 4.9 23.7 7.2 37.7 7.2 8.6 0 16.7-1.6 24.3-4.7 7.2-3.1 13.1-7.4 17.8-12.9 4.3-5.4 6.5-11.9 6.5-19.8 0-10.2-3.8-19-11.3-27.1-7.7-8.1-20.8-13.1-39.2-15.3-17.6-1.8-33.4-6.3-46.7-13.3-13.5-7-24.3-16.4-31.6-27.7-7.4-11.3-11.1-24.4-11.1-38.8 0-15.6 4.5-29.3 13.1-40.6 8.6-11 20.1-19.6 34.5-25.3 7.2-2.9 14.6-5.2 22.5-6.6v-15.3c0-12.6 10.2-22.8 22.8-22.8s23 10.2 23 23v14.7c5.7.9 11 2.3 15.8 4.1 10.8 4 20.1 9.7 27.8 16.9 4.3 4.1 8.3 8.6 11.9 13.5.5.7 1.3 1.4 1.8 2.2 2.3 3.2 3.6 7.4 3.6 11.9 0 11.7-11.7 20.1-21.2 21-9 .9-17.4-9.3-17.4-9.3-3.8-4.9-8.1-9-13.1-12.4-7.5-5-18.1-7.7-31.6-7.9-14.4-.2-26.2 2.7-35.6 8.8-8.6 5.6-12.8 13.5-12.8 24.6 0 5.7 1.3 11.5 4 16.9 2.5 5 7.5 9.7 14.9 13.8 7.7 4.3 19.6 7.4 35 9.2 25.9 2.5 46.7 10.8 62.3 24.6 16 14 24.1 33.1 24.1 56.6-.1 13.5-2.8 25.4-8.2 35.6z" />
                        </g>
                    </svg>
                </span>
            </div>
            <div class="card-content">
                <h6 class=" text-heading mb-2">Monthly Earning</h6>
                <h4 class="text-3xl mb-0 text-textBody">$5014</h4>
                <p class="text-tiny leading-normal mb-0 font-medium"> Based in your local time. </p>
            </div>
        </div>

    </div>

    <!-- chart -->
    <div class="chart-main-wrapper mb-6 grid grid-cols-12 gap-6">
        <div class=" col-span-12 2xl:col-span-7">
            <div class="chart-single bg-white py-3 px-3 sm:py-10 sm:px-10 h-fit rounded-md">
                <h3 class="text-xl">Sales Statics</h3>
                <div class="h-full w-full"><canvas id="salesStatics"></canvas></div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-6 2xl:col-span-5 space-y-6">
            <div class="chart-widget bg-white p-4 sm:p-10 rounded-md">
                <h3 class="text-xl mb-8">Most Selling Category</h3>
                <div class="md:h-[252px] 2xl:h-[398px] w-full"><canvas class="mx-auto md:!w-[240px] md:!h-[240px] 2xl:!w-[360px] 2xl:!h-[360px] " id="earningStatics"></canvas></div>
            </div>

        </div>
    </div>

    <!-- new customers -->
    <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="bg-white p-8 col-span-12 xl:col-span-4 2xl:col-span-3 rounded-md">
            <div class="flex items-center justify-between mb-8">
                <h2 class="font-medium tracking-wide text-slate-700 text-lg mb-0 leading-none">
                    Transactions
                </h2>
                <a href="transaction.html" class="leading-none text-base text-info border-b border-info border-dotted capitalize font-medium hover:text-info/60 hover:border-info/60">View All</a>
            </div>
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between">
                    <div class="m-2 mb:sm-0 flex items-center space-x-3">
                        <div class="avatar">
                            <img class="rounded-full w-10 h-10" src="assets/admin/img/users/user-6.jpg" alt="avatar">
                        </div>
                        <div>
                            <h4 class="text-base text-slate-700 mb-[6px] leading-none">
                                Konnor Guzman
                            </h4>
                            <p class="text-sm text-slate-400 line-clamp-1 m-0 leading-none">
                                Jan 10, 2023 - 06:02 AM
                            </p>
                        </div>
                    </div>
                    <p class="font-medium text-success mb-0">$660.22</p>
                </div>
                <div class="flex flex-wrap items-center justify-between">
                    <div class="m-2 mb:sm-0 flex items-center space-x-3">
                        <div class="avatar">
                            <img class="rounded-full w-10 h-10" src="assets/admin/img/users/user-7.jpg" alt="avatar">
                        </div>
                        <div>
                            <h4 class="text-base text-slate-700 mb-[6px] leading-none">
                                Shahnewaz
                            </h4>
                            <p class="text-sm text-slate-400 line-clamp-1 m-0 leading-none">
                                Jan 15, 2023 - 10:30 AM
                            </p>
                        </div>
                    </div>
                    <p class="font-medium text-danger mb-0">$-80.40</p>
                </div>
                <div class="flex flex-wrap items-center justify-between">
                    <div class="m-2 mb:sm-0 flex items-center space-x-3">
                        <div class="avatar">
                            <img class="rounded-full w-10 h-10" src="assets/admin/img/users/user-8.jpg" alt="avatar">
                        </div>
                        <div>
                            <h4 class="text-base text-slate-700 mb-[6px] leading-none">
                                Steve Smith
                            </h4>
                            <p class="text-sm text-slate-400 line-clamp-1 m-0 leading-none">
                                Feb 01, 2023 - 07:05 PM
                            </p>
                        </div>
                    </div>
                    <p class="font-medium text-success mb-0">$150.00</p>
                </div>
                <div class="flex flex-wrap items-center justify-between">
                    <div class="m-2 mb:sm-0 flex items-center space-x-3">
                        <div class="avatar">
                            <img class="rounded-full w-10 h-10" src="assets/admin/img/users/user-9.jpg" alt="avatar">
                        </div>
                        <div>
                            <h4 class="text-base text-slate-700 mb-[6px] leading-none">
                                Robert Downy
                            </h4>
                            <p class="text-sm text-slate-400 line-clamp-1 m-0 leading-none">
                                Feb 21, 2023 - 11:22 PM
                            </p>
                        </div>
                    </div>
                    <p class="font-medium text-success mb-0">$1482.00</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-8 col-span-12 xl:col-span-8 2xl:col-span-6 rounded-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium tracking-wide text-slate-700 text-lg mb-0 leading-none">Recent Orders</h3>
                <a href="order-list.html" class="leading-none text-base text-info border-b border-info border-dotted capitalize font-medium hover:text-info/60 hover:border-info/60">View All</a>
            </div>

            <!-- table -->
            <div class="overflow-scroll 2xl:overflow-visible">
                <div class="w-[700px] 2xl:w-full">
                    <table class="w-full text-base text-left text-gray-500">
                        <thead class="bg-white">
                            <tr class="border-b border-gray6 text-tiny">
                                <th scope="col" class="pr-8 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Item
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Product ID
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Price
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Status
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold w-14">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Apple MacBook Pro 17"</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #XY-25G
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $2999.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-success px-3 py-1 rounded-md leading-none bg-success/10 font-medium">Active</span>
                                </td>
                                <td class="px-3 py-3 w-14">
                                    <button class="bg-info/10 text-info hover:bg-info hover:text-white inline-block text-center leading-5 text-tiny font-medium py-2 px-4 rounded-md ">
                                        View
                                    </button>

                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Gigabyte Gaming Monitor 4K</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #JK-10A
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $599.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-danger px-3 py-1 rounded-md leading-none bg-danger/10 font-medium">Disabled</span>
                                </td>
                                <td class="px-3 py-3 w-14">
                                    <button class="bg-info/10 text-info hover:bg-info hover:text-white inline-block text-center leading-5 text-tiny font-medium py-2 px-4 rounded-md ">
                                        View
                                    </button>

                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Logitech G502 Hero Mouse</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #LG-502
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $1199.59
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-warning px-3 py-1 rounded-md leading-none bg-warning/10 font-medium">Disabled</span>
                                </td>
                                <td class="px-3 py-3 w-14">
                                    <button class="bg-info/10 text-info hover:bg-info hover:text-white inline-block text-center leading-5 text-tiny font-medium py-2 px-4 rounded-md ">
                                        View
                                    </button>

                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Galaxy S22 Ultra Gray</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #GL-S22
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $1800.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-success px-3 py-1 rounded-md leading-none bg-success/10 font-medium">Active</span>
                                </td>
                                <td class="px-3 py-3 w-14">
                                    <button class="bg-info/10 text-info hover:bg-info hover:text-white inline-block text-center leading-5 text-tiny font-medium py-2 px-4 rounded-md ">
                                        View
                                    </button>

                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white p-8 col-span-12 xl:col-span-12 2xl:col-span-3 rounded-md">
            <h3 class="font-medium tracking-wide text-slate-700 text-lg mb-7 leading-none">Traffics Source</h3>
            <div class="space-y-4">
                <div class="bar">
                    <div class="flex justify-between items-center">
                        <h5 class="text-tiny text-slate-700 mb-0">Facebook</h5>
                        <span class="text-tiny text-slate-700 mb-0">20%</span>
                    </div>
                    <div class="relative h-2 w-full bg-[#3b5998]/10 rounded">
                        <div data-width="20%" class="data-width absolute top-0 h-full rounded bg-[#3b5998] progress-bar "></div>
                    </div>
                </div>
                <div class="bar">
                    <div class="flex justify-between items-center">
                        <h5 class="text-tiny text-slate-700 mb-0">YouTube</h5>
                        <span class="text-tiny text-slate-700 mb-0">80%</span>
                    </div>
                    <div class="relative h-2 w-full bg-[#FF0000]/10 rounded">
                        <div data-width="80%" class="data-width absolute top-0 h-full rounded bg-[#FF0000] progress-bar "></div>
                    </div>
                </div>
                <div class="bar">
                    <div class="flex justify-between items-center">
                        <h5 class="text-tiny text-slate-700 mb-0">WhatsApp</h5>
                        <span class="text-tiny text-slate-700 mb-0">65%</span>
                    </div>
                    <div class="relative h-2 w-full bg-[#25D366]/10 rounded">
                        <div data-width="65%" class="data-width absolute top-0 h-full rounded bg-[#25D366] progress-bar "></div>
                    </div>
                </div>
                <div class="bar">
                    <div class="flex justify-between items-center">
                        <h5 class="text-tiny text-slate-700 mb-0">Instagram</h5>
                        <span class="text-tiny text-slate-700 mb-0">90%</span>
                    </div>
                    <div class="relative h-2 w-full bg-[#C13584]/10 rounded">
                        <div data-width="65%" class="data-width absolute top-0 h-full rounded bg-[#C13584] progress-bar "></div>
                    </div>
                </div>
                <div class="bar">
                    <div class="flex justify-between items-center">
                        <h5 class="text-tiny text-slate-700 mb-0">Others</h5>
                        <span class="text-tiny text-slate-700 mb-0">10%</span>
                    </div>
                    <div class="relative h-2 w-full bg-[#737373]/10 rounded">
                        <div data-width="10%" class="data-width absolute top-0 h-full rounded bg-[#737373] progress-bar "></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- table -->
    <div class="overflow-scroll 2xl:overflow-visible">
        <div class="w-[1400px] 2xl:w-full">
            <div class="grid grid-cols-12 border-b border-gray rounded-t-md bg-white px-10 py-5">
                <div class="table-information col-span-4">
                    <h3 class="font-medium tracking-wide text-slate-800 text-lg mb-0 leading-none">Product List</h3>
                    <p class="text-slate-500 mb-0 text-tiny">Avg. 57 orders per day</p>
                </div>
                <div class="table-actions space-x-9 flex justify-end items-center col-span-8">
                    <div class="table-action-item">
                        <div class="show-category flex items-center  category-select">
                            <span class="text-tiny font-normal text-slate-400 mr-2">Category</span>
                            <select>
                                <option value="">Show All</option>
                                <option value="">Category one</option>
                                <option value="">Category two</option>
                                <option value="">Category three</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-action-item">
                        <div class="show-category flex items-center status-select">
                            <span class="text-tiny font-normal text-slate-400 mr-2">Status</span>
                            <select>
                                <option value="">Show All</option>
                                <option value="">Active</option>
                                <option value="">Pending</option>
                                <option value="">Delivered</option>
                            </select>
                        </div>
                    </div>
                    <div class="w-[250px]">
                        <form action="#">
                            <div class="w-[250px] relative">
                                <input class="input h-9 w-full pr-12" type="text" placeholder="Search Here...">
                                <button class="absolute top-1/2 right-6 translate-y-[-50%] hover:text-theme">
                                    <svg class="-translate-y-px" width="13" height="13" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="">
                <div class="relative rounded-b-md bg-white px-10 py-7 ">
                    <!-- table -->
                    <table class="w-full text-base text-left text-gray-500">
                        <thead class="bg-white">
                            <tr class="border-b border-gray6 text-tiny">
                                <th scope="col" class="pr-8 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Item
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Product ID
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Category
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Price
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase font-semibold">
                                    Status
                                </th>
                                <th scope="col" class="px-3 py-3 text-tiny text-text2 uppercase  font-semibold w-[14%] 2xl:w-[12%]">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Apple MacBook Pro 17"</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #XY-25G
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    Computer
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $2999.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-success px-3 py-1 rounded-md leading-none bg-success/10 font-medium">Active</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center space-x-2">
                                        <button class="bg-success hover:bg-green-600 text-white inline-block text-center leading-5 text-tiny font-medium pt-2 pb-[6px] px-4 rounded-md">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" height="10" viewBox="0 0 492.49284 492" width="10" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="m304.140625 82.472656-270.976563 270.996094c-1.363281 1.367188-2.347656 3.09375-2.816406 4.949219l-30.035156 120.554687c-.898438 3.628906.167969 7.488282 2.816406 10.136719 2.003906 2.003906 4.734375 3.113281 7.527344 3.113281.855469 0 1.730469-.105468 2.582031-.320312l120.554688-30.039063c1.878906-.46875 3.585937-1.449219 4.949219-2.8125l271-270.976562zm0 0" />
                                                    <path fill="currentColor" d="m476.875 45.523438-30.164062-30.164063c-20.160157-20.160156-55.296876-20.140625-75.433594 0l-36.949219 36.949219 105.597656 105.597656 36.949219-36.949219c10.070312-10.066406 15.617188-23.464843 15.617188-37.714843s-5.546876-27.648438-15.617188-37.71875zm0 0" />
                                                </svg>
                                            </span>
                                            Edit
                                        </button>
                                        <button class="bg-white text-slate-700 border border-slate-200 hover:bg-danger hover:border-danger hover:text-white inline-block text-center leading-5 text-tiny font-medium pt-[6px] pb-[5px] px-4  rounded-md ">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" width="10" height="10" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.0697 4.23C17.4597 4.07 15.8497 3.95 14.2297 3.86V3.85L14.0097 2.55C13.8597 1.63 13.6397 0.25 11.2997 0.25H8.67967C6.34967 0.25 6.12967 1.57 5.96967 2.54L5.75967 3.82C4.82967 3.88 3.89967 3.94 2.96967 4.03L0.929669 4.23C0.509669 4.27 0.209669 4.64 0.249669 5.05C0.289669 5.46 0.649669 5.76 1.06967 5.72L3.10967 5.52C8.34967 5 13.6297 5.2 18.9297 5.73C18.9597 5.73 18.9797 5.73 19.0097 5.73C19.3897 5.73 19.7197 5.44 19.7597 5.05C19.7897 4.64 19.4897 4.27 19.0697 4.23Z" fill="currentColor" />
                                                    <path d="M17.2297 7.14C16.9897 6.89 16.6597 6.75 16.3197 6.75H3.67975C3.33975 6.75 2.99975 6.89 2.76975 7.14C2.53975 7.39 2.40975 7.73 2.42975 8.08L3.04975 18.34C3.15975 19.86 3.29975 21.76 6.78975 21.76H13.2097C16.6997 21.76 16.8398 19.87 16.9497 18.34L17.5697 8.09C17.5897 7.73 17.4597 7.39 17.2297 7.14ZM11.6597 16.75H8.32975C7.91975 16.75 7.57975 16.41 7.57975 16C7.57975 15.59 7.91975 15.25 8.32975 15.25H11.6597C12.0697 15.25 12.4097 15.59 12.4097 16C12.4097 16.41 12.0697 16.75 11.6597 16.75ZM12.4997 12.75H7.49975C7.08975 12.75 6.74975 12.41 6.74975 12C6.74975 11.59 7.08975 11.25 7.49975 11.25H12.4997C12.9097 11.25 13.2497 11.59 13.2497 12C13.2497 12.41 12.9097 12.75 12.4997 12.75Z" fill="currentColor" />
                                                </svg>
                                            </span> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Gigabyte Gaming Monitor 4K</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #JK-10A
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    Monitor
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $599.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-danger px-3 py-1 rounded-md leading-none bg-danger/10 font-medium">Disabled</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center space-x-2">
                                        <button class="bg-success hover:bg-green-600 text-white inline-block text-center leading-5 text-tiny font-medium pt-2 pb-[6px] px-4 rounded-md">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" height="10" viewBox="0 0 492.49284 492" width="10" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="m304.140625 82.472656-270.976563 270.996094c-1.363281 1.367188-2.347656 3.09375-2.816406 4.949219l-30.035156 120.554687c-.898438 3.628906.167969 7.488282 2.816406 10.136719 2.003906 2.003906 4.734375 3.113281 7.527344 3.113281.855469 0 1.730469-.105468 2.582031-.320312l120.554688-30.039063c1.878906-.46875 3.585937-1.449219 4.949219-2.8125l271-270.976562zm0 0" />
                                                    <path fill="currentColor" d="m476.875 45.523438-30.164062-30.164063c-20.160157-20.160156-55.296876-20.140625-75.433594 0l-36.949219 36.949219 105.597656 105.597656 36.949219-36.949219c10.070312-10.066406 15.617188-23.464843 15.617188-37.714843s-5.546876-27.648438-15.617188-37.71875zm0 0" />
                                                </svg>
                                            </span>
                                            Edit
                                        </button>
                                        <button class="bg-white text-slate-700 border border-slate-200 hover:bg-danger hover:border-danger hover:text-white inline-block text-center leading-5 text-tiny font-medium pt-[6px] pb-[5px] px-4  rounded-md ">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]"><svg class="-translate-y-px" width="12" height="12" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.0697 4.23C17.4597 4.07 15.8497 3.95 14.2297 3.86V3.85L14.0097 2.55C13.8597 1.63 13.6397 0.25 11.2997 0.25H8.67967C6.34967 0.25 6.12967 1.57 5.96967 2.54L5.75967 3.82C4.82967 3.88 3.89967 3.94 2.96967 4.03L0.929669 4.23C0.509669 4.27 0.209669 4.64 0.249669 5.05C0.289669 5.46 0.649669 5.76 1.06967 5.72L3.10967 5.52C8.34967 5 13.6297 5.2 18.9297 5.73C18.9597 5.73 18.9797 5.73 19.0097 5.73C19.3897 5.73 19.7197 5.44 19.7597 5.05C19.7897 4.64 19.4897 4.27 19.0697 4.23Z" fill="currentColor" />
                                                    <path d="M17.2297 7.14C16.9897 6.89 16.6597 6.75 16.3197 6.75H3.67975C3.33975 6.75 2.99975 6.89 2.76975 7.14C2.53975 7.39 2.40975 7.73 2.42975 8.08L3.04975 18.34C3.15975 19.86 3.29975 21.76 6.78975 21.76H13.2097C16.6997 21.76 16.8398 19.87 16.9497 18.34L17.5697 8.09C17.5897 7.73 17.4597 7.39 17.2297 7.14ZM11.6597 16.75H8.32975C7.91975 16.75 7.57975 16.41 7.57975 16C7.57975 15.59 7.91975 15.25 8.32975 15.25H11.6597C12.0697 15.25 12.4097 15.59 12.4097 16C12.4097 16.41 12.0697 16.75 11.6597 16.75ZM12.4997 12.75H7.49975C7.08975 12.75 6.74975 12.41 6.74975 12C6.74975 11.59 7.08975 11.25 7.49975 11.25H12.4997C12.9097 11.25 13.2497 11.59 13.2497 12C13.2497 12.41 12.9097 12.75 12.4997 12.75Z" fill="currentColor" />
                                                </svg></span> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Logitech G502 Hero Mouse</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #LG-502
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    Accessories
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $1199.59
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-warning px-3 py-1 rounded-md leading-none bg-warning/10 font-medium">Pending</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center space-x-2">
                                        <button class="bg-success hover:bg-green-600 text-white inline-block text-center leading-5 text-tiny font-medium pt-2 pb-[6px] px-4 rounded-md">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" height="10" viewBox="0 0 492.49284 492" width="10" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="m304.140625 82.472656-270.976563 270.996094c-1.363281 1.367188-2.347656 3.09375-2.816406 4.949219l-30.035156 120.554687c-.898438 3.628906.167969 7.488282 2.816406 10.136719 2.003906 2.003906 4.734375 3.113281 7.527344 3.113281.855469 0 1.730469-.105468 2.582031-.320312l120.554688-30.039063c1.878906-.46875 3.585937-1.449219 4.949219-2.8125l271-270.976562zm0 0" />
                                                    <path fill="currentColor" d="m476.875 45.523438-30.164062-30.164063c-20.160157-20.160156-55.296876-20.140625-75.433594 0l-36.949219 36.949219 105.597656 105.597656 36.949219-36.949219c10.070312-10.066406 15.617188-23.464843 15.617188-37.714843s-5.546876-27.648438-15.617188-37.71875zm0 0" />
                                                </svg>
                                            </span>
                                            Edit
                                        </button>
                                        <button class="bg-white text-slate-700 border border-slate-200 hover:bg-danger hover:border-danger hover:text-white inline-block text-center leading-5 text-tiny font-medium pt-[6px] pb-[5px] px-4  rounded-md ">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]"><svg class="-translate-y-px" width="12" height="12" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.0697 4.23C17.4597 4.07 15.8497 3.95 14.2297 3.86V3.85L14.0097 2.55C13.8597 1.63 13.6397 0.25 11.2997 0.25H8.67967C6.34967 0.25 6.12967 1.57 5.96967 2.54L5.75967 3.82C4.82967 3.88 3.89967 3.94 2.96967 4.03L0.929669 4.23C0.509669 4.27 0.209669 4.64 0.249669 5.05C0.289669 5.46 0.649669 5.76 1.06967 5.72L3.10967 5.52C8.34967 5 13.6297 5.2 18.9297 5.73C18.9597 5.73 18.9797 5.73 19.0097 5.73C19.3897 5.73 19.7197 5.44 19.7597 5.05C19.7897 4.64 19.4897 4.27 19.0697 4.23Z" fill="currentColor" />
                                                    <path d="M17.2297 7.14C16.9897 6.89 16.6597 6.75 16.3197 6.75H3.67975C3.33975 6.75 2.99975 6.89 2.76975 7.14C2.53975 7.39 2.40975 7.73 2.42975 8.08L3.04975 18.34C3.15975 19.86 3.29975 21.76 6.78975 21.76H13.2097C16.6997 21.76 16.8398 19.87 16.9497 18.34L17.5697 8.09C17.5897 7.73 17.4597 7.39 17.2297 7.14ZM11.6597 16.75H8.32975C7.91975 16.75 7.57975 16.41 7.57975 16C7.57975 15.59 7.91975 15.25 8.32975 15.25H11.6597C12.0697 15.25 12.4097 15.59 12.4097 16C12.4097 16.41 12.0697 16.75 11.6597 16.75ZM12.4997 12.75H7.49975C7.08975 12.75 6.74975 12.41 6.74975 12C6.74975 11.59 7.08975 11.25 7.49975 11.25H12.4997C12.9097 11.25 13.2497 11.59 13.2497 12C13.2497 12.41 12.9097 12.75 12.4997 12.75Z" fill="currentColor" />
                                                </svg></span> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-white border-b border-gray6 last:border-0 text-start">
                                <td class="pr-8  whitespace-nowrap">
                                    <a href="#" class="font-medium text-heading text-hover-primary">Samsung EVO 500 GB SSD</a>
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    #SM-520
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    Hard Disk
                                </td>
                                <td class="px-3 py-3 font-normal text-slate-600">
                                    $250.00
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px]  text-info px-3 py-1 rounded-md leading-none bg-info/10 font-medium">Delivered</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center space-x-2">
                                        <button class="bg-success hover:bg-green-600 text-white inline-block text-center leading-5 text-tiny font-medium pt-2 pb-[6px] px-4 rounded-md">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" height="10" viewBox="0 0 492.49284 492" width="10" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="m304.140625 82.472656-270.976563 270.996094c-1.363281 1.367188-2.347656 3.09375-2.816406 4.949219l-30.035156 120.554687c-.898438 3.628906.167969 7.488282 2.816406 10.136719 2.003906 2.003906 4.734375 3.113281 7.527344 3.113281.855469 0 1.730469-.105468 2.582031-.320312l120.554688-30.039063c1.878906-.46875 3.585937-1.449219 4.949219-2.8125l271-270.976562zm0 0" />
                                                    <path fill="currentColor" d="m476.875 45.523438-30.164062-30.164063c-20.160157-20.160156-55.296876-20.140625-75.433594 0l-36.949219 36.949219 105.597656 105.597656 36.949219-36.949219c10.070312-10.066406 15.617188-23.464843 15.617188-37.714843s-5.546876-27.648438-15.617188-37.71875zm0 0" />
                                                </svg>
                                            </span>
                                            Edit
                                        </button>
                                        <button class="bg-white text-slate-700 border border-slate-200 hover:bg-danger hover:border-danger hover:text-white inline-block text-center leading-5 text-tiny font-medium pt-[6px] pb-[5px] px-4  rounded-md ">
                                            <span class="text-[9px] inline-block -translate-y-[1px] mr-[1px]">
                                                <svg class="-translate-y-px" width="12" height="12" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.0697 4.23C17.4597 4.07 15.8497 3.95 14.2297 3.86V3.85L14.0097 2.55C13.8597 1.63 13.6397 0.25 11.2997 0.25H8.67967C6.34967 0.25 6.12967 1.57 5.96967 2.54L5.75967 3.82C4.82967 3.88 3.89967 3.94 2.96967 4.03L0.929669 4.23C0.509669 4.27 0.209669 4.64 0.249669 5.05C0.289669 5.46 0.649669 5.76 1.06967 5.72L3.10967 5.52C8.34967 5 13.6297 5.2 18.9297 5.73C18.9597 5.73 18.9797 5.73 19.0097 5.73C19.3897 5.73 19.7197 5.44 19.7597 5.05C19.7897 4.64 19.4897 4.27 19.0697 4.23Z" fill="currentColor" />
                                                    <path d="M17.2297 7.14C16.9897 6.89 16.6597 6.75 16.3197 6.75H3.67975C3.33975 6.75 2.99975 6.89 2.76975 7.14C2.53975 7.39 2.40975 7.73 2.42975 8.08L3.04975 18.34C3.15975 19.86 3.29975 21.76 6.78975 21.76H13.2097C16.6997 21.76 16.8398 19.87 16.9497 18.34L17.5697 8.09C17.5897 7.73 17.4597 7.39 17.2297 7.14ZM11.6597 16.75H8.32975C7.91975 16.75 7.57975 16.41 7.57975 16C7.57975 15.59 7.91975 15.25 8.32975 15.25H11.6597C12.0697 15.25 12.4097 15.59 12.4097 16C12.4097 16.41 12.0697 16.75 11.6597 16.75ZM12.4997 12.75H7.49975C7.08975 12.75 6.74975 12.41 6.74975 12C6.74975 11.59 7.08975 11.25 7.49975 11.25H12.4997C12.9097 11.25 13.2497 11.59 13.2497 12C13.2497 12.41 12.9097 12.75 12.4997 12.75Z" fill="currentColor" />
                                                </svg>
                                            </span>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/chart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script>
@endpush