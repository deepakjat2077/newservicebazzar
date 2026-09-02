# WhatsApp Integration — ServiceBazzar

_How WhatsApp connects, how OTPs go out, and how a customer enquiry reaches a
business owner on WhatsApp. Provider: **Gupshup** (WhatsApp Business API)._

---

## सारांश (quick Hindi overview)

- WhatsApp **Gupshup** के ज़रिए जुड़ा है — कोई अपना WhatsApp app नहीं, एक API है।
- Credentials **Admin panel → Setting → WhatsApp/Gupshup** में भरे जाते हैं (env नहीं)।
- **3 काम** WhatsApp से होते हैं:
  1. **OTP** — login/verify के लिए code WhatsApp पर।
  2. **Enquiry forwarding** — customer जो enquiry भेजता है, admin उसे listing owner के WhatsApp पर template से भेजता है (auto नहीं, admin manually भेजता है)।
  3. **Marketing/Listing message** — किसी listing/owner को template message।
- Owner जब WhatsApp पर **reply** करता है, वो Gupshup **webhook** से वापस आकर Admin panel में chat/replies में दिखता है।
- हर भेजे/आए message की एक row `whatsapp_messages` table में बनती है (sent / delivered / read / failed status समेत)।

---

## 1. Building blocks (कौन-सी फ़ाइलें क्या करती हैं)

| Part | File | Role |
|------|------|------|
| Sender service | `app/Services/WhatsappSender.php` | Gupshup को call करने की एकमात्र जगह — templates, session replies, logging |
| Inbound webhook | `app/Http/Controllers/GupshupWebhookController.php` | Gupshup से आने वाले replies + delivery status यहाँ आते हैं |
| Admin panel | `app/Http/Controllers/Admin/WhatsappController.php` | Enquiries, listings, chats, send, replies screens |
| Mobile API | `app/Http/Controllers/Api/WhatsappApiController.php` | वही काम app के लिए (same sender के ज़रिए) |
| OTP (web) | `app/Http/Controllers/EnquiryOtpController.php`, `UserController@sendMobile2Otp` | WhatsApp OTP भेजना |
| OTP (app) | `app/Http/Controllers/Api/UserAuthController.php` | app login OTP |
| Message model | `app/Models/WhatsappMessage.php` | हर inbound/outbound message की row |
| Config | `config/services.php` → `gupshup` block | API keys / template ids / webhook secret |
| Config loader | `app/Providers/AppServiceProvider.php` | admin settings row से gupshup config भरता है |

---

## 2. Connection — यह जुड़ता कैसे है

1. **Provider = Gupshup.** हम सीधे WhatsApp/Meta से बात नहीं करते; Gupshup की API को HTTP से call करते हैं।
2. **Credentials source.** Values `Admin → Setting → WhatsApp (Gupshup)` में save होती हैं (settings row का `gupshup` column). हर request पर `AppServiceProvider` इन्हें `config('services.gupshup.*')` में डाल देता है। कोई field खाली हो तो `.env` की value fallback रहती है।
3. **ज़रूरी config keys** (`config/services.php`):
   - `api_key` — Gupshup API key (`GUPSHUP_API_KEY`)
   - `app_name` — Gupshup app का नाम (`src.name`)
   - `source` — भेजने वाला WhatsApp business number (digits only)
   - `template_id` — marketing/listing template
   - `otp_template_id` — OTP template
   - `enquiry_template_id` — enquiry-forwarding template (`servicebazzar_enquiry`, 7 variables)
   - `webhook_secret` — inbound webhook को verify करने वाला shared secret
4. **Endpoints Gupshup के:**
   - Template message → `https://api.gupshup.io/wa/api/v1/template/msg`
   - Free-form/session reply → `https://api.gupshup.io/wa/api/v1/msg`
5. **`WhatsappSender::isConfigured()`** true तभी है जब api_key + app_name + source तीनों हों। न हों तो हर send एक "not configured" failure row बनाता है (crash नहीं)।

---

## 3. Message के प्रकार (outbound)

### a) Template messages — `sendTemplate()` / `sendToListing()`
WhatsApp की नीति: किसी को पहली बार या 24 घंटे बाद message भेजने के लिए **approved template** चाहिए। इसलिए OTP, enquiry-forward, और marketing — सब **templates** से जाते हैं।

- `sendToListing($listing, $templateId, $params, $meta)` — एक listing के best WhatsApp/mobile number पर template भेजता है।
- Number चुनने का नियम (`listingPhone()`): पहले WhatsApp number, न हो तो mobile; country code + number; bare 10-digit हो तो आगे `91` लगता है।

### b) Free-form session replies — `sendText()`
Owner ने पिछले **24 घंटे** में हमें message किया हो, तो admin बिना template के plain text reply भेज सकता है (`sessionOpenFor()` check करता है)। 24h बीत जाए तो सिर्फ़ template चलेगा।

हर send के बाद एक `whatsapp_messages` row बनती है — भले सफल हो या fail (fail होने पर `error` में असली कारण)।

---

## 4. Enquiry flow — customer → owner के WhatsApp तक (मुख्य flow)

> **ज़रूरी बात:** enquiry अपने-आप WhatsApp पर नहीं जाती। Customer की enquiry पहले DB में save होती है, फिर **admin** panel से चुनकर owner के WhatsApp पर भेजता है।

```mermaid
flowchart TD
    A[Customer site पर enquiry/quote form भरता है] --> B[Enquiry row DB में save<br/>SiteController / ListingController]
    B --> C[Admin → WhatsApp → Enquiries में दिखती है]
    C --> D[Admin listing चुनता है + enquiry template + Send]
    D --> E[WhatsappController@send → WhatsappSender::sendToListing]
    E --> F[Gupshup template API]
    F --> G[Listing owner के WhatsApp पर message]
    G --> H[Owner WhatsApp पर reply करता है]
    H --> I[Gupshup webhook POST → /gupshup/webhook]
    I --> J[Inbound row save → Admin Chats/Replies में unread badge]
```

**Step-by-step:**
1. **Enquiry बनती है** (customer side) — कोई auto-WhatsApp नहीं; बस `Enquiry::create(...)` (देखें `SiteController` lines ~832/933, `ListingController@requestQuoteStore` ~2312, और mobile API `BrowseController`/`BusinessController`).
2. **Admin panel** `Admin → WhatsApp → Enquiries` में enquiry list होती है। एक enquiry खोलने पर matching listings दिखती हैं।
3. **Admin भेजता है** — `POST admin/whatsapp/send` (`WhatsappController@send`): चुनी हुई `listing_ids` + `template_id` (+ optional custom `params`)।
   - enquiry template के 7 variables `WhatsappSender::enquiryParams($enquiry, $listing)` से भरते हैं:
     `{{1}}` contact person · `{{2}}` listing name · `{{3}}` customer name · `{{4}}` customer phone · `{{5}}` location · `{{6}}` category · `{{7}}` enquiry date-time.
4. हर listing पर एक send होता है; result (sent/failed + reason) admin को JSON में मिलता है, और हर send `whatsapp_messages` में log होता है।
5. **Owner reply** करे तो वो webhook से वापस आकर `Admin → WhatsApp → Chats` में दिखता है (नीचे §6)।

> यही `send` API mobile-app side पर `WhatsappApiController` से भी उपलब्ध है — same `WhatsappSender`, इसलिए दोनों कभी अलग नहीं होते।

---

## 5. OTP flow (WhatsApp पर code)

- **App login/verify:** `Api\UserAuthController@sendOtp/resendOtp` → `WhatsappSender::sendTemplate(otp_template_id, [code])`.
- **Web (profile mobile-2, contact-details verify):** `UserController@sendMobile2Otp`, और business-contact OTP (`ListingController@sendContactOtp`) — सब `otp_template_id` से WhatsApp OTP भेजते हैं।
- OTP का code log row में नहीं छोड़ा जाता (privacy — `log_body` से मास्क होता है)।

---

## 6. Inbound — replies + delivery status (webhook)

Route: **`GET|POST /gupshup/webhook`** (CSRF-exempt, unauthenticated) → `GupshupWebhookController`.

- **Security:** `?token=<secret>` (या `X-Webhook-Token` header) `services.gupshup.webhook_secret` से match होना चाहिए, वरना 401।
- **Response हमेशा खाली 200** होता है (Gupshup की ज़रूरत — कोई JSON/redirect भेजें तो Gupshup callback "invalid" मानकर भेजना बंद कर देता है)। Debug के लिए `?diag=1` पर ही JSON मिलता है।
- **दो तरह के POST आते हैं:**
  1. `message` → user ने reply किया → `direction=inbound` row बनती है (Chats/Replies में unread दिखती है)। Media (image/pdf/audio/video) हमारी अपनी copy में `assets/images/whatsapp/` में save होती है (Gupshup के media links जल्दी expire होते हैं)।
  2. `message-event` → हमारे भेजे message का delivery status (enqueued/sent/delivered/read/failed) — Gupshup की message-id से हमारी outbound row match होकर status update होता है।
- **दो payload formats** support हैं: Gupshup v2 और Meta v3 (`entry[]`) — दोनों handle होते हैं। Duplicate replies (v2+v3 दोनों on होने पर) dedupe होते हैं।
- **Status कभी पीछे नहीं जाता** — delivered/read को कोई देर से आया "sent" event downgrade नहीं करता (RANK guard)।
- हर incoming reply उस last listing से जुड़ता है जिस पर उस number को हमने आख़िरी message भेजा था (`lastListingIdFor`), ताकि panel दिखा सके किसका reply है।

---

## 7. Data model — `whatsapp_messages`

हर row एक message (भेजा या आया):

- `direction` — `outbound` / `inbound`
- `phone`, `contact_name`
- `template_id` (outbound template) या `body` (text/reply/session message)
- `status` — outbound lifecycle: `submitted → sent → delivered → read` (या `failed`); inbound: `received`
- `message_id` — Gupshup की id (delivery status match करने के लिए)
- `error` — fail होने पर कारण
- `media_url` / `media_type` / `media_path` — inbound media
- `listing_id`, `enquiry_id`, `admin_id` — किससे जुड़ा, किसने भेजा
- `payload` — पूरा raw Gupshup payload (debug)

---

## 8. Admin panel screens (routes — `permission:whatsapp.view`, send के लिए `whatsapp.send`)

| Screen | Route |
|--------|-------|
| Dashboard/stats | `admin/whatsapp` |
| Listings (broadcast/template भेजो) | `admin/whatsapp/listings` |
| Enquiries (forward to owner) | `admin/whatsapp/enquiries`, `.../enquiry/{id}` |
| Chats (2-way) | `admin/whatsapp/chats`, `.../chat/{phone}` |
| Replies + unread | `admin/whatsapp/replies` |
| Send (action) | `POST admin/whatsapp/send` |
| Reply in chat (action) | `POST admin/whatsapp/chat/{phone}/reply` |
| Settings (credentials) | `admin/setting/gupshup` |

---

## 9. Setup / go-live checklist

1. **Gupshup account** — WhatsApp business number (source), app name, API key।
2. **Templates approve** कराओ Gupshup में: enquiry (`servicebazzar_enquiry`, 7 vars), OTP, marketing — और उनकी **template id** settings में डालो।
3. **Admin → Setting → WhatsApp (Gupshup)** में api_key / app_name / source / template ids / webhook_secret भरो।
4. **Webhook URL** Gupshup में set करो:
   `https://www.servicebazzar.com/gupshup/webhook?token=<GUPSHUP_WEBHOOK_SECRET>`
   (delivery status v2 format ज़रूरी — तभी sent/delivered/read ticks match होते हैं।)
5. Test: `/gupshup/webhook?diag=1` खोलकर देखो configured/last-post स्थिति।

---

## 10. ध्यान रखने वाली बातें (gotchas)

- **24-hour rule:** owner ने पिछले 24h में reply न किया हो तो plain text नहीं जाएगा — सिर्फ़ approved template।
- **Template approval:** नया/बदला हुआ template Meta से approve होने के बाद ही चलेगा।
- **Webhook response खाली 200 ही रखो** — कुछ और लौटाया तो Gupshup delivery रोक देता है।
- **हर send पैसे/limit खर्च करता है** — इसलिए send endpoints throttled हैं और enquiry auto-forward नहीं है (admin सोच-समझकर भेजता है)।
- **Credentials env में नहीं, admin settings में** — deploy पर env बदलने की ज़रूरत नहीं; admin panel से manage होता है।

---

_Last updated: 2026-09-01. Code references: `WhatsappSender`, `GupshupWebhookController`, `Admin\WhatsappController`, `config/services.php` (gupshup)._
