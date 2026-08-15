{{--
    Admin-written rich content ki poori typography.

    Category pages aur blog details — dono ka content Word/Google Docs se paste
    hota hai, isliye pehle har page apne saath apna font-size, apna rang aur
    apna spacing leke aata tha. App\Support\ContentHtml wo markup hata deta hai,
    aur asli look yahan se aata hai: ek hi jagah, sab pages ke liye ek jaisa,
    desktop aur mobile dono par.

    Kisi bhi page par: content ko <div class="rich-content"> me daalo aur
    @include('themes.servicebazzar.includes.rich-content') kar do.
--}}
@once
<style>
    .rich-content{
        color:#3b3f45;
        font-size:15px;
        line-height:1.7;
    }

    /* Har child ka apna margin hone se hi gaps alag-alag ho rahe the.
       Pehle sab reset, phir sirf neeche ek consistent gap. */
    .rich-content > *{
        margin:0 0 14px;
    }

    .rich-content > *:last-child{
        margin-bottom:0;
    }

    .rich-content h2,
    .rich-content h3,
    .rich-content h4,
    .rich-content h5{
        color:#1c1f23;
        font-weight:700;
        line-height:1.35;
        margin:26px 0 10px;
    }

    .rich-content > h2:first-child,
    .rich-content > h3:first-child,
    .rich-content > h4:first-child{
        margin-top:0;
    }

    .rich-content h2{ font-size:22px; }
    .rich-content h3{ font-size:18px; }
    .rich-content h4,
    .rich-content h5{ font-size:16px; }

    .rich-content p{
        margin:0 0 14px;
    }

    .rich-content ul,
    .rich-content ol{
        margin:0 0 14px;
        padding-left:22px;
    }

    .rich-content li{
        margin:0 0 7px;
    }

    .rich-content li:last-child{
        margin-bottom:0;
    }

    /* Pasted content me links poore paragraph ko apna rang de deti thi. */
    .rich-content a{
        color:#1a73e8;
        text-decoration:none;
    }

    .rich-content a:hover{
        text-decoration:underline;
    }

    .rich-content strong{
        color:#1c1f23;
        font-weight:600;
    }

    .rich-content img{
        max-width:100%;
        height:auto;
    }

    /*
     * Tables.
     *
     * Ye wo hissa hai jo mobile par toot raha tha: table apni natural width par
     * table-layout:auto ke saath render hoti thi, container use clip kar deta
     * tha, aur aakhri column bina kisi scroll ke gayab ho jaata tha.
     *
     * Isliye table ko hamesha ek scroll karne wale wrapper me rakha jaata hai
     * (ContentHtml har table ke around .rich-content-table daal deta hai), aur
     * mobile par columns ko ek minimum width di jaati hai — taki wo sikudne ke
     * bajaye scroll karein.
     */
    .rich-content .rich-content-table{
        width:100%;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        margin:0 0 14px;
    }

    .rich-content table{
        width:100%;
        border-collapse:collapse;
        margin:0;
        display:table;
        background:#fff;
    }

    .rich-content table th,
    .rich-content table td{
        border:1px solid #e3e5e8;
        padding:12px 14px;
        vertical-align:top;
        text-align:left;
    }

    .rich-content table th{
        font-weight:700;
        background:#f7f8f9;
        color:#1c1f23;
    }

    @media (max-width:767px){
        .rich-content{
            font-size:14px;
            line-height:1.65;
        }

        .rich-content h2{ font-size:19px; }
        .rich-content h3{ font-size:17px; }
        .rich-content h4,
        .rich-content h5{ font-size:15px; }

        .rich-content h2,
        .rich-content h3,
        .rich-content h4,
        .rich-content h5{
            margin:20px 0 8px;
        }

        /* Columns ko sikudne se rokta hai, taki wrapper me scroll aaye aur
           aakhri column cut hone ke bajaye swipe karke dikhe. */
        .rich-content table th,
        .rich-content table td{
            min-width:140px;
            padding:10px 12px;
        }

        .rich-content table th:first-child,
        .rich-content table td:first-child{
            min-width:120px;
        }

        /* Scroll kar sakne wali table ka ishara. */
        .rich-content .rich-content-table{
            border-right:1px solid #eceef0;
        }
    }
</style>
@endonce
