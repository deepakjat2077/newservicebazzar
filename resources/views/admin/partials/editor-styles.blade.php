{{--
    Summernote ke andar wahi typography jo site par lagti hai.

    Editor apni default styling use karta tha aur site apni, isliye admin ko
    screen par kuch aur dikhta tha aur publish hone par kuch aur — sabse zyada
    farak paragraph ke gap me aur font size me tha ("ek Enter dabao to bahut
    space aa jata hai", aur "content bahut chhota lag raha hai").

    Ye rules themes/servicebazzar/includes/rich-content.blade.php ki hi copy
    hain, sirf .note-editable par lagi hui. Dono me se ek badlo to doosri bhi
    badalni hai — warna WYSIWYG phir se jhoot bolne lagega.
--}}
<style>
    .note-editor .note-editable{
        color:#333;
        font-size:15px;
        line-height:1.6;
        padding:16px 18px;
    }

    /* Ek Enter = ek paragraph. Gap ek line se thoda kam, taki paragraph alag
       to dikhe par beech me khaali jagah na lage. */
    .note-editor .note-editable > *{
        margin:0 0 12px;
    }

    .note-editor .note-editable > *:last-child{
        margin-bottom:0;
    }

    .note-editor .note-editable p{
        margin:0 0 12px;
    }

    .note-editor .note-editable h2,
    .note-editor .note-editable h3,
    .note-editor .note-editable h4,
    .note-editor .note-editable h5{
        color:#111;
        font-weight:600;
        line-height:1.35;
    }

    /* Heading aur uske paragraph ke beech sirf 4px — site par bhi wahi.
       h3 ko upar wala margin nahi milta. */
    .note-editor .note-editable h2,
    .note-editor .note-editable h4,
    .note-editor .note-editable h5{
        margin:18px 0 4px;
    }

    .note-editor .note-editable h3{
        margin:0 0 4px;
    }

    .note-editor .note-editable > h2:first-child,
    .note-editor .note-editable > h4:first-child{
        margin-top:0;
    }

    .note-editor .note-editable h2{ font-size:18px; }
    .note-editor .note-editable h3{ font-size:16px; }
    .note-editor .note-editable h4,
    .note-editor .note-editable h5{ font-size:15px; }

    .note-editor .note-editable ul,
    .note-editor .note-editable ol{
        margin:0 0 12px;
        padding-left:20px;
    }

    .note-editor .note-editable li{
        margin:0 0 6px;
    }

    .note-editor .note-editable a{
        color:#1a73e8;
        text-decoration:underline;
    }

    .note-editor .note-editable strong{
        color:#1c1f23;
        font-weight:600;
    }

    .note-editor .note-editable img{
        max-width:100%;
        height:auto;
    }

    /* Site par table ke jo borders lagte hain, editor me bhi wahi — warna
       table banate waqt pata hi nahi chalta ki publish hone par kaisi dikhegi. */
    .note-editor .note-editable table{
        width:100%;
        max-width:100%;
        border-collapse:collapse;
        margin:0 0 10px;
        background:#fff;
    }

    .note-editor .note-editable table th,
    .note-editor .note-editable table td{
        border:1px solid #e3e5e8;
        padding:12px 14px;
        vertical-align:top;
        text-align:left;
    }

    .note-editor .note-editable table th{
        font-weight:700;
        background:#f7f8f9;
        color:#1c1f23;
    }

    /* Editing area ko itni jagah do ki lamba content likhte waqt baar baar
       scroll na karna pade. */
    .note-editor .note-editable{
        min-height:320px;
    }
</style>
