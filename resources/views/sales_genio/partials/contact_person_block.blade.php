{{-- One additional contact person. $i is the form index ("__I__" inside the JS template), $p the saved values. --}}
@php $p = $p ?? []; @endphp
<div class="person-block">
    <div class="person-head">
        <span>Additional Contact Person</span>
        <button type="button" class="del" onclick="removePerson(this)" aria-label="Remove this person"><i class="fas fa-trash"></i></button>
    </div>

    <div class="name-row">
        <div class="boxed title-box">
            <label>Title</label>
            <select name="extra[{{ $i }}][title]">
                @foreach(['Mr', 'Ms', 'Mrs', 'Dr'] as $t)
                    <option value="{{ $t }}" @selected(($p['title'] ?? 'Mr') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="boxed name-box">
            <label>Contact Person Name</label>
            <input type="text" name="extra[{{ $i }}][name]" value="{{ $p['name'] ?? '' }}" maxlength="255">
        </div>
    </div>

    <div class="boxed">
        <label>Designation</label>
        <select name="extra[{{ $i }}][designation]">
            @foreach(['Manager', 'Partner', 'Director', 'Owner', 'Proprietor', 'Employee', 'Other'] as $d)
                <option value="{{ $d }}" @selected(($p['designation'] ?? 'Manager') === $d)>{{ $d }}</option>
            @endforeach
        </select>
    </div>

    <div class="mobile-row" style="margin-bottom:16px;">
        <div class="cc">🇮🇳 +91</div>
        <div class="num-box">
            <label>Mobile Number</label>
            <input type="text" name="extra[{{ $i }}][mobile]" value="{{ $p['mobile'] ?? '' }}" maxlength="10" inputmode="numeric" class="xnum">
        </div>
    </div>
</div>
