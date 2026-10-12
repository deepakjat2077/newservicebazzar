<style>
  *{box-sizing:border-box;}
  button,input,select,textarea{font-family:'Poppins',sans-serif;} body{margin:0;background:#f4f6fb;font-family:'Poppins',sans-serif;color:#1a2235;}
  /* No overflow on html/body: it silently kills position:sticky on the header.
     Each block below keeps itself inside its own width instead. */
  .cmp-wrap,.cmp-panel,.cmp-side{max-width:100%;}
  .cmp-wrap{max-width:1180px;margin:0 auto;padding:18px 20px 32px;}
  .cmp-top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
  .cmp-back{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d7dceb;border-radius:22px;
    padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;}
  .cmp-biz{font-size:14px;color:#6b7285;}
  .cmp-biz strong{color:#12203f;font-size:16px;display:block;}
  .cmp-flash{border-radius:11px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:14px;}
  .cmp-flash.ok{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;}
  .cmp-flash.warn{background:#fff7e8;border:1px solid #f2dca6;color:#8a6412;}
  .cmp-layout{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;min-height:0;}
  .cmp-side,.cmp-panel{min-height:0;}

  /* left menu */
  .cmp-side{flex:0 0 260px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:14px 0;max-width:100%;}
  .cmp-side h3{font-size:12.5px;text-transform:none;color:#8a92a6;font-weight:600;margin:6px 0 6px;padding:0 18px;}
  .cmp-side a{display:flex;align-items:center;gap:11px;padding:11px 18px;color:#12203f;text-decoration:none;font-size:14px;
    font-weight:600;border-left:3px solid transparent;}
  .cmp-side a:hover{background:#f7f9fc;}
  .cmp-side a.is-on{border-left-color:#1a73e8;color:#1a73e8;background:#f2f7fe;}
  .cmp-side i{width:18px;text-align:center;color:#6b7285;}
  .cmp-side a.is-on i{color:#1a73e8;}
  .cmp-side .tick{margin-left:auto;color:#1f9d55;font-size:13px;}

  /* right panel */
  .cmp-panel{flex:1;min-width:300px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:26px;}
  .cmp-blank{text-align:center;padding:40px 20px;color:#6b7285;font-size:15px;line-height:1.6;}
  .cmp-blank i{font-size:52px;color:#dbe3f0;display:block;margin-bottom:14px;}
  .cmp-detail{display:flex;gap:28px;flex-wrap:wrap;}
  .cmp-shot{flex:0 0 300px;max-width:100%;}
  .cmp-shot img{width:100%;border-radius:10px;}
  .cmp-info{flex:1;min-width:270px;}
  .cmp-info h1{font-size:21px;font-weight:800;margin:0 0 14px;color:#12203f;}
  .cmp-info h2{font-size:14.5px;font-weight:800;margin:16px 0 10px;color:#1a2235;}
  .cmp-ben{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;}
  .cmp-ben li{display:flex;gap:10px;font-size:13.5px;color:#37415a;line-height:1.45;}
  .cmp-ben i{color:#1f9d55;font-size:16px;flex-shrink:0;}
  .cmp-chip{display:inline-block;background:#eef4fb;color:#1c6fb8;border-radius:8px;padding:7px 13px;font-size:12.5px;
    font-weight:600;margin:10px 8px 0 0;}
  .cmp-plans{display:flex;gap:10px;flex-wrap:wrap;}
  .cmp-plan{position:relative;min-width:112px;border:1px solid #e6e9f2;background:#f8f9fc;border-radius:11px;padding:12px 15px;cursor:pointer;margin:0;}
  .cmp-plan input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .cmp-plan.is-on{border-color:#1a73e8;background:#eef6fd;}
  .cmp-plan.is-on:after{content:"✓";position:absolute;top:-9px;right:-8px;width:22px;height:22px;border-radius:50%;
    background:#fff;border:1px solid #1a73e8;color:#1a73e8;font-size:12px;line-height:20px;text-align:center;}
  .cmp-plan span.l{display:block;font-size:12.5px;color:#6b7285;margin-bottom:4px;}
  .cmp-plan.is-on span.l{color:#1a73e8;}
  .cmp-plan span.p{display:block;font-size:18px;font-weight:800;color:#12203f;}
  .cmp-loc{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;}
  .cmp-loc select{border:1px solid #dfe3ec;border-radius:9px;padding:9px 12px;font-size:13.5px;min-width:150px;}
  .cmp-actions{display:flex;gap:12px;margin-top:22px;flex-wrap:wrap;}
  .cmp-skip{flex:0 0 140px;text-align:center;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:10px;
    padding:12px;font-weight:700;text-decoration:none;font-size:14px;}
  .cmp-add{flex:1;min-width:180px;background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:12px;font-weight:700;
    font-size:14px;cursor:pointer;}
  .cmp-note{display:flex;gap:8px;background:#fff8e8;border:1px solid #f2dca6;color:#8a6412;border-radius:10px;
    padding:11px 13px;font-size:12.5px;margin-top:16px;}

  /* checklist + cart */
  .cmp-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:20px;margin-top:18px;}
  .cmp-card h2{font-size:15.5px;font-weight:800;margin:0 0 4px;color:#12203f;}
  .cmp-card p.hint{color:#6b7285;font-size:13px;margin:0 0 12px;}
  .cmp-list{list-style:none;padding:0;margin:0;}
  .cmp-list li{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f3f8;font-size:13.5px;}
  .cmp-list li:last-child{border-bottom:0;}
  .cmp-list .ok i{color:#1f9d55;}
  .cmp-list .no i{color:#e0b021;}
  .cmp-list .grow{flex:1;}
  .cmp-list .why{display:block;color:#8a92a6;font-size:12px;margin-top:2px;}
  .cmp-fix{border:1px solid #1a73e8;color:#1a73e8;border-radius:8px;padding:5px 12px;font-size:12.5px;font-weight:700;text-decoration:none;}
  .cmp-cart td{padding:9px 0;border-bottom:1px solid #f1f3f8;font-size:13.5px;}
  .cmp-cart td:last-child{text-align:right;font-weight:700;}
  .cmp-cart tr:last-child td{border-bottom:0;}
  .cmp-total{display:flex;justify-content:space-between;padding-top:12px;font-size:15px;font-weight:800;color:#12203f;}
  .cmp-dim{opacity:.45;pointer-events:none;}
  .cmp-missing{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
  .cmp-missing a{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #f0d49a;color:#8a6412;
    border-radius:8px;padding:7px 12px;font-size:12.5px;font-weight:700;text-decoration:none;}
  .cmp-missing a:hover{background:#fffaf0;}
  .cmp-x{background:none;border:0;color:#b4261f;cursor:pointer;font-size:13px;}

  /* popup */
  .cmp-modal{position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;padding:20px;z-index:80;}
  .cmp-modal__box{background:#fff;border-radius:14px;max-width:520px;width:100%;box-shadow:0 20px 50px rgba(16,24,40,.25);}
  .cmp-modal__head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eef0f6;}
  .cmp-modal__head strong{font-size:15px;color:#12203f;}
  .cmp-modal__head button{background:none;border:0;font-size:20px;color:#8a92a6;cursor:pointer;line-height:1;}
  .cmp-modal__body{padding:18px 20px;}
  .cmp-modal__body p{margin:0 0 12px;font-size:13.5px;color:#37415a;line-height:1.5;}
  .cmp-modal__foot{display:flex;gap:12px;padding:0 20px 20px;}
  .cmp-now{flex:1;text-align:center;background:#1a73e8;color:#fff;border-radius:9px;padding:11px;font-weight:700;text-decoration:none;font-size:14px;}
  @media (max-width:820px){.cmp-side{flex-basis:100%;}}

  /* area picker + position step */
  .ar-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
  .ar-head h1{font-size:20px;font-weight:800;margin:0 0 4px;color:#12203f;}
  .ar-head p{margin:0;color:#8a92a6;font-size:13px;}
  .ar-search{position:relative;}
  .ar-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9aa2b4;font-size:13px;}
  .ar-search input{border:1px solid #dfe3ec;border-radius:9px;padding:9px 12px 9px 34px;font-size:13.5px;min-width:210px;}
  .ar-list{max-height:360px;overflow-y:auto;border:1px solid #eef0f6;border-radius:12px;}
  .ar-row{position:relative;display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid #f1f3f8;cursor:pointer;margin:0;}
  .ar-row:last-child{border-bottom:0;}
  .ar-row input{position:absolute;left:0;top:0;width:1px;height:1px;opacity:0;}
  .ar-all{border:1px solid #eef0f6;border-radius:12px;margin-bottom:10px;background:#fbfcfe;}
  .ar-tick{flex:0 0 20px;width:20px;height:20px;border-radius:50%;border:1.5px solid #cfd6e4;display:flex;align-items:center;
    justify-content:center;color:transparent;font-size:10px;margin-top:2px;}
  .ar-row.is-on .ar-tick{background:#1a73e8;border-color:#1a73e8;color:#fff;}
  .ar-body strong{display:block;font-size:14.5px;color:#12203f;font-weight:700;}
  .ar-sub{display:block;color:#8a92a6;font-size:12.5px;margin-top:3px;}
  .ar-sub b{color:#1a73e8;}
  .ar-empty{color:#8a92a6;font-size:14px;padding:30px 0;text-align:center;}
  .pos-bar{display:flex;gap:30px;flex-wrap:wrap;border-bottom:1px solid #eef0f6;padding-bottom:16px;margin-bottom:6px;}
  .pos-bar span{display:block;font-size:12px;color:#8a92a6;margin-bottom:5px;}
  .pos-bar strong{font-size:15px;color:#12203f;}
  .pos-bar select{border:1px solid #dfe3ec;border-radius:8px;padding:7px 12px;font-size:14px;font-weight:700;color:#12203f;background:#fff;}
  .pos-budget{display:flex;justify-content:space-between;align-items:center;background:#f7f9fc;border-radius:10px;
    padding:14px 16px;margin:14px 0;}
  .pos-budget span{font-size:14.5px;font-weight:700;color:#12203f;}
  .pos-budget strong{font-size:20px;color:#1a73e8;}
  .pos-links{display:flex;gap:18px;margin-bottom:14px;}
  .pos-links a{color:#1a73e8;font-size:13.5px;font-weight:700;text-decoration:none;}
  .pos-table-wrap{max-height:300px;overflow-y:auto;border:1px solid #eef0f6;border-radius:10px;}
  .pos-table{width:100%;border-collapse:collapse;}
  .pos-table thead th{position:sticky;top:0;z-index:1;}
  .pos-table th{background:#fafbfc;color:#6b7280;font-size:11.5px;text-transform:uppercase;letter-spacing:.3px;
    text-align:left;padding:10px 12px;}
  .pos-table td{padding:12px;border-top:1px solid #f1f3f8;font-size:13.5px;color:#37415a;}
  .pos-table i{color:#1f9d55;margin-right:7px;}
  .pos-summary{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#f7f9fc;border-radius:10px;
    padding:13px 16px;margin-top:16px;font-size:13.5px;color:#37415a;}
  .pos-summary strong{color:#12203f;}

  /* ---------- phones ---------- */
  @media (max-width:760px){
    .cmp-wrap{padding:14px 12px 44px !important;}
    .cmp-top{gap:10px;margin-bottom:12px;}
    .cmp-biz{text-align:left;}
    .cmp-layout{gap:12px;}

    /* package menu becomes a scrolling strip of chips */
    .cmp-side{flex:1 1 100%;display:flex;gap:8px;overflow-x:auto;padding:10px;border-radius:12px;
      -webkit-overflow-scrolling:touch;}
    .cmp-side h3{display:none;}
    .cmp-side a{flex:0 0 auto;white-space:nowrap;border:1px solid #e6e9f2;border-radius:22px;padding:9px 14px;
      font-size:13px;border-left:1px solid #e6e9f2;}
    .cmp-side a.is-on{border-color:#1a73e8;background:#eef4fd;}
    .cmp-side .tick{margin-left:6px;}

    .cmp-panel{padding:16px 14px;border-radius:12px;}
    .cmp-panel h1{font-size:18px !important;}
    .cmp-detail{gap:16px;}
    .cmp-shot{flex:1 1 100%;}
    .cmp-shot img{max-height:320px;object-fit:contain;}
    .cmp-info{flex:1 1 100%;min-width:0;}
    .cmp-info h1{font-size:19px;}
    .cmp-plans{gap:8px;}
    .cmp-plan{flex:1 1 calc(50% - 4px);min-width:0;padding:11px 12px;}
    .cmp-plan span.p{font-size:16px;}
    .cmp-actions{gap:10px;}
    .cmp-skip,.cmp-add{flex:1 1 100%;}

    /* area picker */
    .ar-head{gap:10px;}
    .ar-head h1{font-size:18px;}
    .ar-search input{width:100%;min-width:0;}
    .ar-search{flex:1 1 100%;}
    .ar-list{max-height:none;display:block;}
    .ar-row{padding:12px 13px;border-right:0;}
    .ar-body strong{font-size:14px;}

    /* position + budget */
    .pb-grid{gap:14px;}
    .pb-main{min-width:0 !important;flex:1 1 100%;}
    .pb-side{position:static !important;flex:1 1 100% !important;}
    .pb-pos{grid-template-columns:1fr 1fr !important;gap:10px;}
    .pb-card{padding:13px;}
    .pb-card .rate{font-size:17px;}
    .pb-chips{gap:8px;}
    .pb-chip{padding:8px 14px;font-size:12.5px;}
    .pb-arealist{grid-template-columns:1fr !important;max-height:none;}
    .pb-areas{max-height:none;}
    .pb-total strong{font-size:21px;}

    /* review */
    .rv-grid{gap:14px;}
    .rv-main,.rv-side{flex:1 1 100% !important;min-width:0;}
    .rv-table th,.rv-table td{padding:10px;font-size:12.5px;}
    .rv-free{flex-wrap:wrap;}
    .rv-free img{width:100%;}
    .rv-free .pill{margin-left:0;}
  }
</style>
