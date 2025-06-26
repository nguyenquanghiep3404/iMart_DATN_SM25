@extends('users.layouts.app')

@section('title', 'About - iMart')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Hero -->
      <section class="container pt-3 pt-sm-4">
        <div class="position-relative">
          <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark rtl-flip" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
          <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark rtl-flip" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
          <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7 col-xl-5 offset-xl-1 py-5">
              <div class="px-4 px-sm-5 px-xl-0 pe-lg-4">
                <h1 class="text-center text-sm-start mb-4">How can we help?</h1>
                <form class="d-flex flex-column flex-sm-row gap-2">
                  <input type="search" class="form-control form-control-lg" placeholder="What do you need help with?" aria-label="Search field">
                  <button type="submit" class="btn btn-lg btn-primary px-3">
                    <i class="ci-search fs-lg ms-n2 ms-sm-0"></i>
                    <span class="ms-2 d-sm-none">Search</span>
                  </button>
                </form>
                <div class="nav gap-2 pt-3 pt-sm-4 mt-1 mt-sm-0">
                  <span class="nav-link text-body-secondary pe-none p-0 me-1">Common topics:</span>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="help-single-article-v2.html">payments</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="help-single-article-v2.html">refunds</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="help-single-article-v2.html">delivery</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="help-single-article-v2.html">dashboard</a>
                </div>
              </div>
            </div>
            <div class="col-lg-5 offset-xl-1 d-none d-lg-block">
              <div class="ratio rtl-flip" style="--cz-aspect-ratio: calc(356 / 526 * 100%)">
                <img src="assets/img/help/hero-light.png" class="d-none-dark" alt="Image">
                <img src="assets/img/help/hero-dark.png" class="d-none d-block-dark" alt="Image">
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- Help topics -->
      <section class="container py-5">
        <div class="row g-0 pt-md-2 pt-xl-4">
          <div class="col-md-4 col-lg-3 pb-2 pb-sm-3 pb-md-0 mb-4 mb-md-0">
            <h2 class="h5 border-bottom pb-3 pb-sm-4 mb-0">Help topics</h2>

            <!-- Nav tabs in format of list group -->
            <div class="list-group list-group-borderless pt-4 pe-md-4" role="tablist">
              <a class="list-group-item list-group-item-action d-flex align-items-center active" href="#delivery" data-bs-toggle="list" role="tab" aria-controls="delivery" id="delivery-tab" aria-selected="true">
                <i class="ci-delivery fs-base opacity-75 me-2"></i>
                Delivery
              </a>
              <a class="list-group-item list-group-item-action d-flex align-items-center" href="#returns" data-bs-toggle="list" role="tab" aria-controls="returns" id="returns-tab" aria-selected="false" tabindex="-1">
                <i class="ci-refresh-cw fs-base opacity-75 me-2"></i>
                Returns &amp; refunds
              </a>
              <a class="list-group-item list-group-item-action d-flex align-items-center" href="#payment" data-bs-toggle="list" role="tab" aria-controls="payment" id="payment-tab" aria-selected="false" tabindex="-1">
                <i class="ci-credit-card fs-base opacity-75 me-2"></i>
                Payment options
              </a>
              <a class="list-group-item list-group-item-action d-flex align-items-center" href="#order" data-bs-toggle="list" role="tab" aria-controls="order" id="order-tab" aria-selected="false" tabindex="-1">
                <i class="ci-shopping-bag fs-base opacity-75 me-2"></i>
                Order issues
              </a>
              <a class="list-group-item list-group-item-action d-flex align-items-center" href="#stock" data-bs-toggle="list" role="tab" aria-controls="stock" id="stock-tab" aria-selected="false" tabindex="-1">
                <i class="ci-archive fs-base opacity-75 me-2"></i>
                Products &amp; stock
              </a>
              <a class="list-group-item list-group-item-action d-flex align-items-center" href="#account" data-bs-toggle="list" role="tab" aria-controls="account" id="account-tab" aria-selected="false" tabindex="-1">
                <i class="ci-settings fs-base opacity-75 me-2"></i>
                Managing account
              </a>
            </div>

          </div>
          <div class="col-md-8 col-lg-9">

            <!-- Tabs with links -->
            <div class="tab-content">

              <!-- Delivery tab -->
              <div class="tab-pane show active" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Delivery</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How does courier delivery work?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What happens if I'm not available to receive the delivery?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I track my order in real-time?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Is there an option for express delivery?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Will my parcel be charged customs charges?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any restrictions on shipping certain products internationally?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I request a specific delivery time?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I cancel or modify my order before it's shipped?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What is your policy on lost or damaged items during delivery?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How are shipping costs calculated?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any additional fees for expedited delivery services?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I schedule a return pick-up for a product?</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Returns tab -->
              <div class="tab-pane" id="returns" role="tabpanel" aria-labelledby="returns-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Returns &amp; refunds</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I initiate a return for a product?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What is your return policy?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I exchange a product instead of getting a refund?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How long does it take to process a refund?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What should I do if my returned item is damaged or defective?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any items that are not eligible for returns?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I return a gift that was purchased for me?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Is there a restocking fee for returned items?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I track the status of my return?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What do I do if I receive the wrong item in my order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are shipping costs refundable for returned items?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you provide return labels for international customers?</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Payment tab -->
              <div class="tab-pane" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Payment options</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What payment methods do you accept?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I change the payment method after placing an order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are my credit card details secure on your website?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you offer installment payment options?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I use a promo code or gift card during checkout?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any additional fees for using certain payment methods?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I split the payment between two different cards?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What currencies do you accept for international orders?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Is it safe to save my payment information for future purchases?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Why was my payment declined?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I request a refund for an overcharged amount?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you offer cash-on-delivery (COD) as a payment option?</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Order tab -->
              <div class="tab-pane" id="order" role="tabpanel" aria-labelledby="order-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Order issues</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I track the status of my order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What should I do if my order is delayed?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I cancel my order after it has been placed?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What happens if an item in my order is out of stock?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I add or remove items from my existing order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you offer expedited shipping options?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">My tracking information is not updating; what should I do?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I change the shipping address for my order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What if my order is missing items or is incomplete?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I report a problem with my delivered package?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I request a gift receipt with my order?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any restrictions on shipping to certain locations?</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Stock tab -->
              <div class="tab-pane" id="stock" role="tabpanel" aria-labelledby="stock-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Products &amp; stock</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How often is your product inventory updated?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I pre-order items that are out of stock?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are your products authentic and original?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What should I do if an item I want is out of stock?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you restock popular or sold-out items?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I modify or customize a product before purchasing?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are there any warranties on your products?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I sign up for product restock notifications?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I review products on your website?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What is the difference between in-stock and pre-order items?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Are the product images on your website accurate representations?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you offer bulk or wholesale discounts?</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Account tab -->
              <div class="tab-pane" id="account" role="tabpanel" aria-labelledby="account-tab">
                <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                  <h2 class="h5 mb-0">Managing account</h2>
                </div>
                <div class="position-relative">
                  <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                  <ul class="nav flex-column gap-3 pt-4 ps-md-4">
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I create an account on your website?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I change my email address associated with my account?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What should I do if I forgot my password?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I update my personal information on my account?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Is my account information secure on your website?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I have multiple shipping addresses saved in my account?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I unsubscribe from newsletters or promotional emails?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Can I delete my account, and what happens to my order history?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How do I check the status of my recent orders?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">Do you offer a guest checkout option without creating an account?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">How can I subscribe or unsubscribe from SMS notifications?</a>
                    </li>
                    <li>
                      <a class="nav-link hover-effect-underline fw-normal p-0" href="help-single-article-v2.html">What benefits come with creating an account on your website?</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact CTA -->
        <div class="pt-4 pb-1 pb-sm-3 pb-md-4 pb-xl-5 mt-2 mt-sm-3">
          <h3 class="fs-sm pb-sm-1">Can't find an answer to your question?</h3>
          <a class="btn btn-lg btn-primary" href="#!">Contact us</a>
        </div>
      </section>


      <!-- Popular articles (Carousel) -->
      <section class="bg-body-tertiary py-5">
        <div class="container py-1 py-sm-2 py-md-3 py-lg-4 py-xl-5">
          <h2 class="text-center pb-2 pb-sm-3 pb-lg-4">Popular articles</h2>

          <!-- Nav pills -->
          <div class="row g-0 overflow-x-auto pb-3 mb-2 mb-md-3 mb-lg-4">
            <div class="col-auto mx-auto">
              <ul class="nav nav-pills flex-nowrap text-nowrap">
                <li class="nav-item">
                  <a class="nav-link rounded active" aria-current="page" href="#!">Delivery</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link rounded" href="#!">Returns &amp; refunds</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link rounded" href="#!">Payment</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link rounded" href="#!">Order issues</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link rounded" href="#!">Products &amp; stock</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link rounded" href="#!">Account</a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Carousel -->
          <div class="position-relative mx-2 mx-sm-0">
            <div class="swiper" data-swiper="{
              &quot;slidesPerView&quot;: 1,
              &quot;spaceBetween&quot;: 24,
              &quot;loop&quot;: true,
              &quot;autoHeight&quot;: true,
              &quot;navigation&quot;: {
                &quot;prevEl&quot;: &quot;.btn-prev&quot;,
                &quot;nextEl&quot;: &quot;.btn-next&quot;
              },
              &quot;breakpoints&quot;: {
                &quot;500&quot;: {
                  &quot;slidesPerView&quot;: 2
                },
                &quot;992&quot;: {
                  &quot;slidesPerView&quot;: 3
                }
              }
            }">
              <div class="swiper-wrapper">

                <!-- Article -->
                <article class="swiper-slide">
                  <a class="ratio d-flex hover-effect-scale rounded overflow-hidden" href="help-single-article-v2.html" style="--cz-aspect-ratio: calc(306 / 416 * 100%)">
                    <img src="assets/img/help/article01.jpg" class="hover-effect-target" alt="Image">
                  </a>
                  <div class="pt-4">
                    <div class="text-body-tertiary fs-xs pb-2 mt-n1 mb-1">October 2, 2024</div>
                    <h3 class="h5 mb-0">
                      <a class="hover-effect-underline" href="help-single-article-v2.html">When should I place an order to ensure Express Delivery?</a>
                    </h3>
                  </div>
                </article>

                <!-- Article -->
                <article class="swiper-slide">
                  <a class="ratio d-flex hover-effect-scale rounded overflow-hidden" href="help-single-article-v2.html" style="--cz-aspect-ratio: calc(306 / 416 * 100%)">
                    <img src="assets/img/help/article02.jpg" class="hover-effect-target" alt="Image">
                  </a>
                  <div class="pt-4">
                    <div class="text-body-tertiary fs-xs pb-2 mt-n1 mb-1">July 17, 2024</div>
                    <h3 class="h5 mb-0">
                      <a class="hover-effect-underline" href="help-single-article-v2.html">Why does my statement have a recurring delivery charge?</a>
                    </h3>
                  </div>
                </article>

                <!-- Article -->
                <article class="swiper-slide">
                  <a class="ratio d-flex hover-effect-scale rounded overflow-hidden" href="help-single-article-v2.html" style="--cz-aspect-ratio: calc(306 / 416 * 100%)">
                    <img src="assets/img/help/article03.jpg" class="hover-effect-target" alt="Image">
                  </a>
                  <div class="pt-4">
                    <div class="text-body-tertiary fs-xs pb-2 mt-n1 mb-1">June 13, 2024</div>
                    <h3 class="h5 mb-0">
                      <a class="hover-effect-underline" href="help-single-article-v2.html">How can I find information about your international delivery?</a>
                    </h3>
                  </div>
                </article>

                <!-- Article -->
                <article class="swiper-slide">
                  <a class="ratio d-flex hover-effect-scale rounded overflow-hidden" href="help-single-article-v2.html" style="--cz-aspect-ratio: calc(306 / 416 * 100%)">
                    <img src="assets/img/help/article04.jpg" class="hover-effect-target" alt="Image">
                  </a>
                  <div class="pt-4">
                    <div class="text-body-tertiary fs-xs pb-2 mt-n1 mb-1">May 30, 2024</div>
                    <h3 class="h5 mb-0">
                      <a class="hover-effect-underline" href="help-single-article-v2.html">Will my parcel be charged additional customs charges?</a>
                    </h3>
                  </div>
                </article>
              </div>
            </div>

            <!-- Prev button -->
            <div class="position-absolute top-50 start-0 z-2 translate-middle hover-effect-target mt-n5">
              <button type="button" class="btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start" aria-label="Prev">
                <i class="ci-chevron-left fs-lg animate-target"></i>
              </button>
            </div>

            <!-- Next button -->
            <div class="position-absolute top-50 start-100 z-2 translate-middle hover-effect-target mt-n5">
              <button type="button" class="btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end" aria-label="Next">
                <i class="ci-chevron-right fs-lg animate-target"></i>
              </button>
            </div>
          </div>
        </div>
      </section>


      <!-- FAQ (Accordion) -->
      <section class="container py-5">
        <div class="row py-1 py-sm-2 py-md-3 py-lg-4 py-xl-5">
          <div class="col-md-4 col-xl-3 mb-4 mb-md-0" style="margin-top: -120px">
            <div class="sticky-md-top text-center text-md-start pe-md-4 pe-lg-5 pe-xl-0" style="padding-top: 120px;">
              <h2>Popular FAQs</h2>
              <p class="pb-2 pb-md-3">Still have unanswered questions and need to get in touch?</p>
              <a class="btn btn-lg btn-primary" href="#!">Contact us</a>
            </div>
          </div>
          <div class="col-md-8 offset-xl-1">

            <!-- Accordion of questions -->
            <div class="accordion" id="faq">

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-1">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-1" aria-expanded="false" aria-controls="faqCollapse-1">
                    <span class="me-2">How long will delivery take?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-1" aria-labelledby="faqHeading-1" data-bs-parent="#faq">
                  <div class="accordion-body">Delivery times vary based on your location and the chosen shipping method. Generally, our standard delivery takes up to 5 days, while our Express Delivery ensures your order reaches you within 1 day. Please note that these times may be subject to occasional variations due to unforeseen circumstances, but we do our best to meet these estimates.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-2">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-2" aria-expanded="false" aria-controls="faqCollapse-2">
                    <span class="me-2">What payment methods do you accept?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-2" aria-labelledby="faqHeading-2" data-bs-parent="#faq">
                  <div class="accordion-body">We offer a range of secure payment options to provide you with flexibility and convenience. Accepted methods include major credit/debit cards, PayPal, and other secure online payment gateways. You can find the complete list of accepted payment methods during the checkout process.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-3">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-3" aria-expanded="false" aria-controls="faqCollapse-3">
                    <span class="me-2">Do you ship internationally?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-3" aria-labelledby="faqHeading-3" data-bs-parent="#faq">
                  <div class="accordion-body">Yes, we proudly offer international shipping to cater to our global customer base. Shipping costs and delivery times will be automatically calculated at the checkout based on your selected destination. Please note that any customs duties or taxes applicable in your country are the responsibility of the customer.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-4">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-4" aria-expanded="false" aria-controls="faqCollapse-4">
                    <span class="me-2">Do I need an account to place an order?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-4" aria-labelledby="faqHeading-4" data-bs-parent="#faq">
                  <div class="accordion-body">While you can place an order as a guest, creating an account comes with added benefits. By having an account, you can easily track your orders, manage your preferences, and enjoy a quicker checkout process for future purchases. It also allows us to provide you with personalized recommendations and exclusive offers.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-5">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-5" aria-expanded="false" aria-controls="faqCollapse-5">
                    <span class="me-2">How can I track my order?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-5" aria-labelledby="faqHeading-5" data-bs-parent="#faq">
                  <div class="accordion-body">Once your order is dispatched, you will receive a confirmation email containing a unique tracking number. You can use this tracking number on our website to monitor the real-time status of your shipment. Additionally, logging into your account will grant you access to a comprehensive order history, including tracking information.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-6">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-6" aria-expanded="false" aria-controls="faqCollapse-6">
                    <span class="me-2">What are the product refund conditions?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-6" aria-labelledby="faqHeading-6" data-bs-parent="#faq">
                  <div class="accordion-body">Our refund policy is designed to ensure customer satisfaction. Details can be found in our [refund policy page](insert link). In essence, we accept returns within [insert number] days of receiving the product, provided it is in its original condition with all tags and packaging intact. Refunds are processed promptly once the returned item is inspected and approved.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-7">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-7" aria-expanded="false" aria-controls="faqCollapse-7">
                    <span class="me-2">Where can I find your size guide?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-7" aria-labelledby="faqHeading-7" data-bs-parent="#faq">
                  <div class="accordion-body">Our comprehensive size guide is conveniently located on each product page to assist you in choosing the right fit. Additionally, you can find the size guide in the main menu under "Size Guide." We recommend referring to these resources to ensure your selected items match your preferred sizing.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-8">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-8" aria-expanded="false" aria-controls="faqCollapse-8">
                    <span class="me-2">Do I need to create an account to shop with you?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-8" aria-labelledby="faqHeading-8" data-bs-parent="#faq">
                  <div class="accordion-body">While guest checkout is available for your convenience, creating an account enhances your overall shopping experience. With an account, you can easily track your order status, save multiple shipping addresses, and enjoy a streamlined checkout process. Moreover, account holders receive early access to promotions and exclusive offers. Signing up is quick and hassle-free!</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-9">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-9" aria-expanded="false" aria-controls="faqCollapse-9">
                    <span class="me-2">Is there a minimum order value for free shipping?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-9" aria-labelledby="faqHeading-9" data-bs-parent="#faq">
                  <div class="accordion-body">Yes, we offer free shipping on orders exceeding $250. Orders below this threshold are subject to standard shipping fees, which will be displayed during the checkout process.</div>
                </div>
              </div>

              <!-- Question -->
              <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading-10">
                  <button type="button" class="accordion-button hover-effect-underline collapsed" data-bs-toggle="collapse" data-bs-target="#faqCollapse-10" aria-expanded="false" aria-controls="faqCollapse-10">
                    <span class="me-2">Can I modify or cancel my order after placing it?</span>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="faqCollapse-10" aria-labelledby="faqHeading-10" data-bs-parent="#faq">
                  <div class="accordion-body">Once an order is confirmed, our system processes it promptly to ensure timely dispatch. Therefore, modifications or cancellations are challenging after this point. However, please contact our customer support as soon as possible, and we will do our best to assist you based on the order status.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
@endsection