{{--
    Admin-written rich content ki poori typography.

    Category pages aur blog details — dono ka content Word/Google Docs se paste
    hota hai, isliye pehle har page apne saath apna font-size, apna rang aur
    apna spacing leke aata tha. App\Support\ContentHtml wo markup hata deta hai,
    aur asli look yahan se aata hai: ek hi jagah, sab pages ke liye ek jaisa,
    desktop aur mobile dono par.

    Kisi bhi page par: content ko <div class="rich-content"> me daalo aur
    @include('themes.servicebazzar.includes.rich-content') kar do.

    Selector me class do baar (.rich-content.rich-content) jaan-boojh kar hai.
    Har page ka apna CSS block hai (.category-content-body, .privacy-policy-
    content, .orrrr) aur unki specificity single class ki thi — yaani jo baad
    me load hota tha wahi jeet jaata tha. Isliye ye typography kisi page par
    lagti thi aur kisi par nahi. Double class har single-class page rule se
    upar rehti hai, bina kisi page ki CSS ko chhue.
--}}
@once
<style>
    /*
     * Body text headings se chhota hona chahiye.
     *
     * Pehle ye 17px tha jabki h3 16px hai — yaani paragraph heading se bada,
     * aur isi wajah se headings alag hi nahi lag rahi thi. 15px rakhne se
     * scale sahi ban jaati hai: 18 (h2) > 16 (h3) > 15 (body).
     */
    .rich-content.rich-content{
        color:#333;
        font-size:15px;
        line-height:1.6;
    }

    /*
     * font-size sirf container par lagana kaafi NAHI hai.
     *
     * Inheritance CSS me sabse kamzor cheez hai — kisi bhi page ka
     * `.privacy-policy-content p { font-size:16px }` jaisa seedha rule use
     * haraa deta hai, chahe specificity kam ho. Isi wajah se ek page par
     * paragraph 12px ka ho gaya tha aur heading (16px) se bhi chhota dikhne
     * laga tha. Isliye har text element par size/rang seedha set hota hai.
     */
    .rich-content.rich-content p,
    .rich-content.rich-content li,
    .rich-content.rich-content span,
    .rich-content.rich-content div,
    .rich-content.rich-content blockquote{
        font-size:13px;
        font-weight:400;
        line-height:1.6;
        color:#333;
    }

    /* Har child ka apna margin hone se hi gaps alag-alag ho rahe the.
       Pehle sab reset, phir sirf neeche ek consistent gap.
       12px = ek line se thoda kam, taki Enter dabane par paragraph alag to
       dikhe par beech me khaali jagah na lage. */
    .rich-content.rich-content > *{
        margin:0 0 12px;
    }

    .rich-content.rich-content > *:last-child{
        margin-bottom:0;
    }

    .rich-content.rich-content h2,
    .rich-content.rich-content h3,
    .rich-content.rich-content h4,
    .rich-content.rich-content h5{
        color:#111;
        font-weight:600;
        line-height:1.35;
    }

    /*
     * Heading aur uske neeche wale paragraph ke beech bahut kam gap (4px) —
     * yahi cheez JustDial jaisi tight dikhti hai. Pehle ye 10px tha, jisse
     * heading apne text se kat ke alag lag rahi thi.
     *
     * Upar ka 18px sirf h2/h4/h5 ko milta hai; h3 ke upar ka gap pichhle
     * paragraph ke margin-bottom se hi aa jaata hai.
     */
    .rich-content.rich-content h2,
    .rich-content.rich-content h4,
    .rich-content.rich-content h5{
        margin:18px 0 4px;
    }

    .rich-content.rich-content h3{
        margin:0 0 4px;
    }

    .rich-content.rich-content > h2:first-child,
    .rich-content.rich-content > h4:first-child{
        margin-top:0;
    }

    .rich-content.rich-content h2{ font-size:18px; }
    .rich-content.rich-content h3{ font-size:16px; }
    .rich-content.rich-content h4,
    .rich-content.rich-content h5{ font-size:15px; }

    .rich-content.rich-content p{
        margin:0 0 12px;
    }

    /*
     * Theme globally `ul, ol { list-style: none; padding: 0 }` karta hai
     * (style.css:412). Content ki lists me bullets zaroori hain, isliye yahan
     * wapas laaye ja rahe hain — bina iske list ke items sirf indent hoke
     * plain lines jaise dikhte hain.
     */
    .rich-content.rich-content ul,
    .rich-content.rich-content ol{
        margin:0 0 12px;
        padding-left:22px;
        list-style-position:outside;
    }

    .rich-content.rich-content ul{
        list-style-type:disc;
    }

    .rich-content.rich-content ol{
        list-style-type:decimal;
    }

    .rich-content.rich-content li{
        display:list-item;
        margin:0 0 6px;
    }

    .rich-content.rich-content ul ul{
        list-style-type:circle;
        margin:6px 0 0;
    }

    .rich-content.rich-content li:last-child{
        margin-bottom:0;
    }

    .rich-content.rich-content .rich-content-spacer{
        display:block;
        height:28px;
        min-height:28px;
        margin:0;
    }

    /*
     * Links underline ke saath, taki paragraph ke beech me wo click karne
     * layak dikhein — sirf rang se pata nahi chalta.
     */
    .rich-content.rich-content a{
        color:#1a73e8;
        text-decoration:underline;
    }

    .rich-content.rich-content a:hover{
        text-decoration:underline;
    }

    .rich-content.rich-content strong{
        color:#1c1f23;
        font-weight:600;
    }

    .rich-content.rich-content img{
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
    .rich-content.rich-content .rich-content-table{
        width:100%;
        max-width:100%;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        margin:0 0 10px;
    }

    .rich-content.rich-content table{
        width:100%;
        max-width:100%;
        border-collapse:collapse;
        margin:0;
        display:table;
        background:#fff;
    }

    .rich-content.rich-content table th,
    .rich-content.rich-content table td{
        border:1px solid #e3e5e8;
        padding:12px 14px;
        vertical-align:top;
        text-align:left;
    }

    .rich-content.rich-content table th{
        font-weight:700;
        background:#f7f8f9;
        color:#1c1f23;
    }

    @media (max-width:767px){
        .rich-content.rich-content,
        .rich-content.rich-content p,
        .rich-content.rich-content li,
        .rich-content.rich-content span,
        .rich-content.rich-content div,
        .rich-content.rich-content blockquote{
            font-size:14.5px;
            line-height:1.6;
        }

        /*
         * Heading sizes mobile par bhi wahi rehte hain — 18px / 16px pehle se
         * hi chhote hain, aur inhe aur ghata dene par wo body text (15.5px) se
         * alag hi nahi lagte. Sirf gap thoda kam hota hai.
         *
         * h3 yahan bhi margin se bahar hai, desktop ki tarah.
         */
        .rich-content.rich-content h2,
        .rich-content.rich-content h4,
        .rich-content.rich-content h5{
            margin:20px 0 8px;
        }

        /*
         * Side-scroll wali table try ki gayi thi, lekin phone par scrollbar
         * / cut-off columns dikhna hi nahi chahiye — poori table ek nazar me
         * screen ke andar fit honi chahiye, chahe cells thodi lambi ho jayein.
         *
         * table-layout:fixed columns ko barabar baant deta, isliye har column
         * ko percentage width diya gaya hai: pehla column (service/label,
         * aam taur par sabse lamba text) baaki se zyada chaudा, taaki uska
         * text kam se kam lines me wrap ho.
         */
        .rich-content.rich-content table{
            table-layout:fixed;
            width:100%;
        }

        .rich-content.rich-content table th,
        .rich-content.rich-content table td{
            padding:8px 7px;
            font-size:13px;
            line-height:1.45;
            word-wrap:break-word;
            overflow-wrap:break-word;
            hyphens:auto;
        }

        .rich-content.rich-content table th:first-child,
        .rich-content.rich-content table td:first-child{
            width:38%;
        }
    }
</style>
@endonce
