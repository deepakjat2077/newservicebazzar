<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Details – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #1a1a2e; padding-bottom: 90px; }
        .page { max-width: 760px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; padding: 16px 18px;
            border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 17px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .content { padding: 18px; }
        .info-note {
            background: #eef3fb; border-radius: 8px; padding: 12px 14px; font-size: 13px;
            color: #555; margin-bottom: 22px; display: flex; align-items: flex-start; gap: 8px;
        }
        .info-note i { color: #1a73e8; margin-top: 2px; }

        .boxed {
            border: 1.5px solid #d5dae5; border-radius: 8px; padding: 8px 14px; margin-bottom: 16px;
        }
        .boxed label { font-size: 11px; color: #888; display: block; margin-bottom: 2px; }
        .boxed input, .boxed select {
            border: none; outline: none; width: 100%; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e; background: transparent;
        }

        .name-row { display: flex; gap: 12px; align-items: flex-start; }
        .name-row .title-box { width: 90px; flex-shrink: 0; }
        .name-row .name-box { flex: 1; }

        .add-link { color: #1a73e8; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-block; margin: 4px 0 22px; }

        .section-divider { height: 1px; background: #eef0f4; margin: 22px 0; }

        .person-block { border: 1px dashed #c3cbdc; border-radius: 12px; padding: 14px 14px 2px; margin: 0 0 18px; background: #fafbff; }
        .person-block .boxed, .person-block .num-box, .person-block .cc { background: #fff; }
        .person-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; font-weight: 600; color: #12203f; }
        .person-head .del { background: none; border: none; color: #e53935; font-size: 16px; cursor: pointer; padding: 4px; }

        .mobile-block { margin-bottom: 20px; }
        .mobile-row { display: flex; gap: 12px; align-items: flex-start; }
        .mobile-row .cc {
            border: 1.5px solid #d5dae5; border-radius: 8px; padding: 12px 10px;
            display: flex; align-items: center; gap: 6px; font-size: 14px; white-space: nowrap; flex-shrink: 0;
        }
        .mobile-row .num-box { flex: 1; border: 1.5px solid #d5dae5; border-radius: 8px; padding: 8px 14px; }
        .mobile-row .num-box label { font-size: 11px; color: #888; display: block; }
        .mobile-row .num-box input { border: none; outline: none; width: 100%; font-size: 15px; font-family: 'Poppins', sans-serif; }
        .mobile-row .del { background: none; border: none; color: #e53935; font-size: 16px; cursor: pointer; padding: 12px 4px; }
        .mobile-opts { display: flex; gap: 20px; flex-wrap: wrap; margin: 10px 2px 0; }
        .mobile-opts label { font-size: 12px; color: #555; display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .mobile-opts input { accent-color: #1a73e8; }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-save {
            display: block; width: 100%; max-width: 760px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-save:hover { background: #1557b0; }
        .err { color: #e53935; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">Contact Details</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.contact.update', $business->id) }}" id="contactForm">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Update your contact details to stay in touch with your customers in real time.</span>
            </div>

            @if($errors->any())<div class="err" style="margin-bottom:14px;">{{ $errors->first() }}</div>@endif

            <div class="name-row">
                <div class="boxed title-box">
                    <label>Title</label>
                    <select name="contact_title">
                        @foreach(['Mr','Ms','Mrs','Dr'] as $t)
                            <option value="{{ $t }}" @selected(($business->contact_title ?? 'Mr') == $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="boxed name-box">
                    <label>Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $business->contact_person) }}">
                </div>
            </div>

            <div class="boxed">
                <label>Designation</label>
                <select name="designation">
                    @php $desig = old('designation', $business->designation ?? 'Owner'); @endphp
                    @foreach(['Owner','Manager','Partner','Director','Proprietor','Employee','Other'] as $d)
                        <option value="{{ $d }}" @selected($desig == $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            {{-- More people to reach at this business --}}
            @php $extraPeople = array_values((array) old('extra', $business->extra_contacts ?? [])); @endphp
            <div id="personList">
                @foreach($extraPeople as $i => $person)
                    @include('sales_genio.partials.contact_person_block', ['i' => $i, 'p' => $person])
                @endforeach
            </div>
            <span class="add-link" id="addPersonLink" onclick="addPerson()"><i class="fas fa-plus"></i> Add Another Contact Person</span>

            <template id="personTpl">
                @include('sales_genio.partials.contact_person_block', ['i' => '__I__', 'p' => []])
            </template>

            <div class="section-divider"></div>

            <div id="mobileList">
                @php
                    $nums = is_array($business->mobile_numbers) && count($business->mobile_numbers)
                        ? $business->mobile_numbers : [$business->mobile];
                    $nums = array_values(array_filter($nums));
                    if (empty($nums)) $nums = [''];
                    $primary = $business->mobile ?: ($nums[0] ?? '');
                @endphp
                @foreach($nums as $i => $num)
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="cc">🇮🇳 +91</div>
                            <div class="num-box">
                                <label>Mobile Number</label>
                                <input type="text" name="mobiles[]" value="{{ $num }}" maxlength="10" inputmode="numeric" class="mnum">
                            </div>
                            <button type="button" class="del" onclick="removeMobile(this)"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="mobile-opts">
                            <label><input type="radio" name="primary_mobile_pick" value="{{ $num }}" {{ $num == $primary ? 'checked' : '' }} onclick="setPrimary(this)"> Set as Primary No.</label>
                            <label><input type="checkbox" checked> Receive Customer Calls</label>
                            <label><input type="checkbox" {{ $i == 0 ? 'checked' : '' }}> Receive App Notifications</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <input type="hidden" name="primary_mobile" id="primaryMobile" value="{{ $primary }}">
            <span class="add-link" onclick="addMobile()"><i class="fas fa-plus"></i> Add Another Mobile Number</span>

            <div class="section-divider"></div>

            {{-- WhatsApp Numbers --}}
            <div id="whatsappList">
                @php $waNums = is_array($business->whatsapp_numbers) ? array_values(array_filter($business->whatsapp_numbers)) : []; @endphp
                @forelse($waNums as $wa)
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="cc">🇮🇳 +91</div>
                            <div class="num-box">
                                <label>WhatsApp Number</label>
                                <input type="text" name="whatsapps[]" value="{{ $wa }}" maxlength="10" inputmode="numeric" class="mnum">
                            </div>
                            <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="mobile-opts"><label><input type="checkbox" checked> Receive Customer Messages</label></div>
                    </div>
                @empty
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="cc">🇮🇳 +91</div>
                            <div class="num-box">
                                <label>WhatsApp Number</label>
                                <input type="text" name="whatsapps[]" value="" maxlength="10" inputmode="numeric" class="mnum">
                            </div>
                            <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="mobile-opts"><label><input type="checkbox" checked> Receive Customer Messages</label></div>
                    </div>
                @endforelse
            </div>
            <span class="add-link" onclick="addWhatsapp()"><i class="fas fa-plus"></i> Add Another WhatsApp Number</span>

            <div class="section-divider"></div>

            {{-- Emails --}}
            <div id="emailList">
                @php
                    $allEmails = array_values(array_filter(array_merge([$business->email], is_array($business->extra_emails) ? $business->extra_emails : [])));
                    if (empty($allEmails)) $allEmails = [''];
                @endphp
                @foreach($allEmails as $i => $em)
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="num-box">
                                <label>Email</label>
                                <input type="email" name="emails[]" value="{{ $em }}">
                            </div>
                            <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="mobile-opts"><label><input type="checkbox" {{ $i == 0 ? 'checked' : '' }}> Receive Notifications</label></div>
                    </div>
                @endforeach
            </div>
            <span class="add-link" onclick="addEmail()"><i class="fas fa-plus"></i> Add Another Email</span>

            <div class="section-divider"></div>

            {{-- Landline --}}
            <div id="landlineList">
                @php $llNums = is_array($business->landline_numbers) ? array_values(array_filter($business->landline_numbers)) : []; @endphp
                @foreach($llNums as $ll)
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="num-box"><label>Landline Number</label><input type="text" name="landlines[]" value="{{ $ll }}"></div>
                            <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <span class="add-link" onclick="addLandline()"><i class="fas fa-plus"></i> Add Landline Number</span>

            <div class="section-divider"></div>

            {{-- Toll-Free --}}
            <div id="tollfreeList">
                @php $tfNums = is_array($business->toll_free_numbers) ? array_values(array_filter($business->toll_free_numbers)) : []; @endphp
                @foreach($tfNums as $tf)
                    <div class="mobile-block">
                        <div class="mobile-row">
                            <div class="num-box"><label>Toll-Free Number</label><input type="text" name="tollfrees[]" value="{{ $tf }}"></div>
                            <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <span class="add-link" onclick="addTollfree()"><i class="fas fa-plus"></i> Add Toll-Free Number</span>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    // Further contact people: each block's fields are named extra[N][...], N never reused.
    const MAX_PEOPLE = 5;
    let personNext = document.querySelectorAll('#personList .person-block').length;

    function syncPersonLink() {
        const count = document.querySelectorAll('#personList .person-block').length;
        document.getElementById('addPersonLink').style.display = count >= MAX_PEOPLE ? 'none' : '';
    }

    function addPerson() {
        if (document.querySelectorAll('#personList .person-block').length >= MAX_PEOPLE) return;
        const html = document.getElementById('personTpl').innerHTML.replace(/__I__/g, personNext++);
        document.getElementById('personList').insertAdjacentHTML('beforeend', html);
        syncPersonLink();
        const added = document.querySelectorAll('#personList .person-block');
        added[added.length - 1].querySelector('input[type="text"]').focus();
    }

    function removePerson(btn) {
        btn.closest('.person-block').remove();
        syncPersonLink();
    }

    // Digits only, for people added now or later.
    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('xnum')) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        }
    });
    syncPersonLink();

    function addMobile() {
        const block = document.createElement('div');
        block.className = 'mobile-block';
        block.innerHTML = `
            <div class="mobile-row">
                <div class="cc">🇮🇳 +91</div>
                <div class="num-box">
                    <label>Mobile Number</label>
                    <input type="text" name="mobiles[]" maxlength="10" inputmode="numeric" class="mnum">
                </div>
                <button type="button" class="del" onclick="removeMobile(this)"><i class="fas fa-trash"></i></button>
            </div>
            <div class="mobile-opts">
                <label><input type="radio" name="primary_mobile_pick" value="" onclick="setPrimary(this)"> Set as Primary No.</label>
                <label><input type="checkbox" checked> Receive Customer Calls</label>
                <label><input type="checkbox"> Receive App Notifications</label>
            </div>`;
        document.getElementById('mobileList').appendChild(block);
        bindDigits();
    }

    function removeMobile(btn) {
        const blocks = document.querySelectorAll('.mobile-block');
        if (blocks.length <= 1) { alert('At least one mobile number is required.'); return; }
        btn.closest('.mobile-block').remove();
    }

    function setPrimary(radio) {
        document.getElementById('primaryMobile').value = radio.closest('.mobile-block').querySelector('.mnum').value;
    }

    function bindDigits() {
        document.querySelectorAll('.mnum').forEach(el => {
            el.oninput = () => {
                el.value = el.value.replace(/\D/g, '');
                // keep primary radio value in sync with typed number
                const radio = el.closest('.mobile-block').querySelector('input[name="primary_mobile_pick"]');
                if (radio) { radio.value = el.value; if (radio.checked) document.getElementById('primaryMobile').value = el.value; }
            };
        });
    }
    bindDigits();

    function addWhatsapp() {
        const b = document.createElement('div');
        b.className = 'mobile-block';
        b.innerHTML = `
            <div class="mobile-row">
                <div class="cc">🇮🇳 +91</div>
                <div class="num-box"><label>WhatsApp Number</label><input type="text" name="whatsapps[]" maxlength="10" inputmode="numeric" class="mnum"></div>
                <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
            </div>
            <div class="mobile-opts"><label><input type="checkbox" checked> Receive Customer Messages</label></div>`;
        document.getElementById('whatsappList').appendChild(b);
        bindDigits();
    }

    function addEmail() {
        const b = document.createElement('div');
        b.className = 'mobile-block';
        b.innerHTML = `
            <div class="mobile-row">
                <div class="num-box"><label>Email</label><input type="email" name="emails[]"></div>
                <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
            </div>
            <div class="mobile-opts"><label><input type="checkbox"> Receive Notifications</label></div>`;
        document.getElementById('emailList').appendChild(b);
    }

    function addLandline() {
        const b = document.createElement('div');
        b.className = 'mobile-block';
        b.innerHTML = `
            <div class="mobile-row">
                <div class="num-box"><label>Landline Number</label><input type="text" name="landlines[]"></div>
                <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
            </div>`;
        document.getElementById('landlineList').appendChild(b);
    }

    function addTollfree() {
        const b = document.createElement('div');
        b.className = 'mobile-block';
        b.innerHTML = `
            <div class="mobile-row">
                <div class="num-box"><label>Toll-Free Number</label><input type="text" name="tollfrees[]"></div>
                <button type="button" class="del" onclick="this.closest('.mobile-block').remove()"><i class="fas fa-trash"></i></button>
            </div>`;
        document.getElementById('tollfreeList').appendChild(b);
    }
</script>
</body>
</html>
