@extends($themeView.'layouts.site')

@section('title','Service Keywords')

@section('content')



<style>
@media (max-width: 767px){
    .category-area{
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .category-area .category-grid{
        margin-top: 0 !important;
    }
}
.font14 {
    font-size: 14px;
}.fw400 {
    font-weight: 400;
}.color111 {
    color: #111;
}

.category-item .font14{
    font-size: 11px;
    line-height: 14px;
    margin-top: 6px;

    text-align: center;
    max-width: 72px;
    height: 28px;

    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;

    word-break: break-word;
    overflow-wrap: break-word;
}

.category-item a{
    align-items: center;
}
</style>
    
<section class="custom-right category-area py-5">
    <div class="container-fildu">

     

        <div class="category-grid mt-4">

        @foreach($categories as $index => $categoryItem)
   
    <div class="category-item text-center {{ $index >= 12 ? 'hide-mobile' : '' }}">
         <a href="{{ route('service.keywords.category', $categoryItem->slug) }}"
           class="text-decoration-none text-dark "
           data-slug="{{ $categoryItem->slug }}">

            <div class="category-box d-flex align-items-center justify-content-center size">
                @if($categoryItem->icon && Str::endsWith(strtolower($categoryItem->icon), ['.png', '.jpg', '.jpeg', '.svg', '.webp']))
                    <img src="{{ asset('assets/images/category/icons/' . $categoryItem->icon) }}"
                       loading="lazy"  alt="{{ $categoryItem->name }}"
                         class="category-icon-img">
                @else
                    <i class="{{ $categoryItem->icon }}"></i>
                @endif
            </div>
<div class="font14 color111 fw400">{{ $categoryItem->name }}</div>
      
        </a>
    </div>
@endforeach

<style>
    .category-box {
    position: relative;
}

.category-box .category-icon-img {
    width: 38px;
    height: 38px;
    object-fit: contain;
}

.category-box i {
    font-size: 32px;
}
    @media (max-width: 767px){
    .hide-mobile{
        display: none !important;
    }
}
.more{
font-size:40px; color:red;
}
</style>

           

        </div>
    </div>
</section>



  

   <style>

/* DESKTOP + COMMON */
.category-grid{
    display: grid;
    grid-template-columns: repeat(10, minmax(0, 1fr));
    gap: 30px 20px;
    width: 100%;
}

.category-item{
    text-align: center;
    min-width: 0;
}

.category-item a,
.category-item button{
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    text-decoration: none;
    padding: 0;
    margin: 0;
    border: 0;
    background: transparent;
    box-sizing: border-box;
}

.category-box{
   display: inline-block;
    overflow: hidden;
    border: .5px solid #ccc;
    -webkit-border-radius: 14px;
    -moz-border-radius: 14px;
    border-radius: 14px;
    height: 74px;
    width: 74px;
    display: -webkit-inline-box;
    display: -webkit-inline-flex;
    display: -moz-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
}
.category-box i{
    font-size: 34px;
    color: red;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-title{
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    margin: 0;
    text-align: center;
    min-height: 44px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    word-break: break-word;
    overflow-wrap: break-word;
}

.more-btn{
    background: transparent;
    border: 0;
    padding: 0;
    margin: 0;
    cursor: pointer;
}

/* TABLET */
@media (max-width: 991px){
    .category-grid{
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px 12px;
    }
}

/* CATEGORY GRID FIX - MOBILE */
@media (max-width: 767px){

     .category-grid{
        gap: 16px 8px;
    }

     .category-item .font14{
        font-size: 11px;
        line-height: 1.25;
        max-width: 72px;
        min-height: 30px;
        margin-top: 4px;

        display: block;
        text-align: center;

        overflow: hidden;
        word-break: normal;
    }

    .category-item a,
    .category-item button{
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    .category-box{
        width: 62px;
        height: 62px;
        margin-bottom: 6px;
    }

     .category-box .category-icon-img{
        width: 34px;
        height: 34px;
        object-fit: contain;
    }

    .category-box i{
        font-size: 20px !important;
        line-height: 1 !important;
    }

    .category-title{
        font-size: 9px !important;
        line-height: 1.25 !important;
        margin-top: 0 !important;
        text-align: center !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        min-height: 32px !important;
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 0 2px !important;
    }

    .category-area{
        padding-bottom: 20px !important;
        overflow: hidden !important;
    }

    .custom-right{
        overflow-x: hidden !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .hide-mobile{
        display: none !important;
    }

    /* ================= POPUP FIX ================= */

    .category-popup{
        position: fixed;
        top: 0;
        right: -100%;
        width: 100% !important;
        max-width: 100% !important;
        height: 100%;
        background: #fff;
        z-index: 99999999;
        padding: 14px 10px !important;
        box-sizing: border-box !important;
        overflow-y: auto;
        overflow-x: hidden !important;
        transition: 0.3s ease;
    }

    .category-popup.active{
        z-index: 100000;
        right: 0 !important;
    }

    .popup-grid{
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 14px 6px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .popup-item{
        min-width: 0 !important;
        width: 100% !important;
        padding: 8px 2px !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
        text-align: center !important;
    }

    .popup-item i{
        font-size: 22px !important;
        margin-bottom: 6px !important;
    }

    .popup-item span{
        font-size: 12px !important;
        line-height: 1.3 !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        display: block !important;
        text-align: center !important;
    }
}

.pb-15 {
    padding-bottom: 15px;
}
.pt-15 {
    padding-top: 15px;
}
.p-30 {
    padding: 30px;
}

.font18 {
    font-size: 18px;
}
.fw500 {
    font-weight: 500;
}
.color111 {
    color: #111;
}

</style>
    

</div>

@endsection