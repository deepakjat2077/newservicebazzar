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
        font-size:17px;
        line-height:1.75;
    }

    /* Har child ka apna margin hone se hi gaps alag-alag ho rahe the.
       Pehle sab reset, phir sirf neeche ek consistent gap.
       10px = ek line se thoda kam, taki Enter dabane par paragraph alag to
       dikhe par beech me khaali jagah na lage. */
    .rich-content > *{
        margin:0 0 10px;
    }

    .rich-content > *:last-child{
        margin-bottom:0;
    }

    .rich-content h2,
    .rich-content h3,
    .rich-content h4,
    .rich-content h5{
        color:#111;
        font-weight:600;
        line-height:1.35;
    }

    /* h3 ko upar wala margin nahi milta — wo `> *` wale 0 0 10px par hi
       rehta hai, taki uske upar khaali jagah na aaye. */
    .rich-content h2,
    .rich-content h4,
    .rich-content h5{
        margin:26px 0 10px;
    }

    .rich-content > h2:first-child,
    .rich-content > h4:first-child{
        margin-top:0;
    }

    .rich-content h2{ font-size:18px; }
    .rich-content h3{ font-size:16px; }
    .rich-content h4,
    .rich-content h5{ font-size:15px; }

    .rich-content p{
        margin:0 0 10px;
    }

    .rich-content ul,
    .rich-content ol{
        margin:0 0 10px;
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
     * Mobile par table toot rahi thi: apni natural width par render hoti thi,
     * container use clip kar deta tha, aur aakhri column gayab ho jaata tha.
     *
     * Wrapper (ContentHtml har table ke around .rich-content-table daal deta
     * hai) sirf safety ke liye hai — bahut zyada columns wali table ke liye.
     * Aam table mobile par scroll nahi karti, balki table-layout:fixed se
     * sikud kar poori screen me fit ho jaati hai: 3 columns padhne ke liye
     * side me swipe karna padhne se zyada takleef deta hai.
     */
    .rich-content .rich-content-table{
        width:100%;
        max-width:100%;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        margin:0 0 10px;
    }

    .rich-content table{
        width:100%;
        max-width:100%;
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
            font-size:15.5px;
            line-height:1.7;
        }

        /*
         * Heading sizes mobile par bhi wahi rehte hain — 18px / 16px pehle se
         * hi chhote hain, aur inhe aur ghata dene par wo body text (15.5px) se
         * alag hi nahi lagte. Sirf gap thoda kam hota hai.
         *
         * h3 yahan bhi margin se bahar hai, desktop ki tarah.
         */
        .rich-content h2,
        .rich-content h4,
        .rich-content h5{
            margin:20px 0 8px;
        }

        /*
         * Table screen me fit hoti hai, scroll nahi karti.
         *
         * table-layout:fixed columns ko barabar baant deta hai, aur text wrap
         * hokar neeche chala jaata hai — isliye teeno columns ek saath dikhte
         * hain, bas font thoda chhota ho jaata hai.
         */
        .rich-content table{
            table-layout:fixed;
        }

        .rich-content table th,
        .rich-content table td{
            padding:8px 7px;
            font-size:13px;
            line-height:1.45;
            word-wrap:break-word;
            overflow-wrap:break-word;
            hyphens:auto;
        }
    }
</style>
@endonce
