<!-- Topbar start -->
<div class="topbar">
    <div class="topbar-left bar-toggle">
        <span class="top-bar-icon"></span>
    </div>

    <div class="topbar-right">

        <!-- Admin Notifications -->
        <div class="admin-notification dropdown mr-3">
            <a href="javascript:void(0)"
               class="dropdown-toggle position-relative"
               id="adminNotifyDropdown"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">

                <i class="las la-bell" style="font-size:26px;"></i>

                <span id="adminNotifyBadge"
                      class="badge badge-danger"
                      style="
                          position:absolute;
                          top:-7px;
                          right:-9px;
                          display:none;
                      ">
                    0
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-right p-0"
                 aria-labelledby="adminNotifyDropdown"
                 style="width:320px;">

                <div class="px-3 py-2 border-bottom font-weight-bold">
                    Notifications
                </div>

                <a href="{{ route('admin.listing.pending') }}"
                   class="dropdown-item d-flex justify-content-between">

                    <span>Pending Listings</span>

                    <span id="pendingListingCount"
                          class="badge badge-danger">
                        {{ $pendingListings ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.enquiry.index') }}"
                   class="dropdown-item d-flex justify-content-between">

                    <span>New Enquiries</span>

                    <span id="newEnquiryCount"
                          class="badge badge-warning">
                        {{ $pendingEnquiries ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.guest.chats.index') }}"
                   class="dropdown-item d-flex justify-content-between">

                    <span>Guest Chats</span>

                    <span id="guestChatCount"
                          class="badge badge-info">
                        {{ $unreadGuestChats ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.listing.update.pending') }}"
                   class="dropdown-item d-flex justify-content-between">

                    <span>Update Listings</span>

                    <span id="updateListingCount"
                          class="badge badge-danger">
                        {{ $pendingUpdateListings ?? 0 }}
                    </span>
                </a>

            </div>
        </div>

        <!-- Profile dropdown -->
        <div class="profile-dropdown-area dropdown">
            <a href="javascript:void(0)"
               class="dropdown-toggle"
               id="dropdownMenuButton"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">

                <img src="{{ loadImage('assets/central/images/' . auth()->guard('admin')->user()->image) }}">
            </a>

            <div class="dropdown-menu pb-0 pt-0"
                 aria-labelledby="dropdownMenuButton">

                <a class="dropdown-item"
                   href="{{ route('admin.profile') }}">
                    <i class="fas fa-user"></i>
                    @lang('Profile')
                </a>

                <a class="dropdown-item"
                   href="{{ route('admin.password.change') }}">
                    <i class="fas fa-key"></i>
                    @lang('Change Password')
                </a>

                <a class="dropdown-item"
                   href="{{ route('admin.logout') }}">
                    <i class="fas fa-sign-out-alt"></i>
                    @lang('Logout')
                </a>
            </div>
        </div>

    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<!-- Laravel Echo -->
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    window.Pusher = Pusher;

    Pusher.logToConsole = true;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: @json(config('broadcasting.connections.pusher.key')),
        cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
        forceTLS: true,
        encrypted: true
    });
</script>


<!-- Notification audio -->
<audio id="adminNotifySound"
       preload="auto"
       loop>
</audio>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * SweetAlert mein ek time par ek notification show hogi.
     * Baaki notifications queue mein save hongi.
     */
    let notificationQueue = [];
    let notificationPopupActive = false;


    /*
     * Element ka current numeric count return karta hai.
     */
    function getCount(id) {
        const element = document.getElementById(id);

        if (!element) {
            console.warn('Count element not found:', id);
            return 0;
        }

        return parseInt(element.textContent.trim(), 10) || 0;
    }


    /*
     * Individual notification count update karta hai.
     */
    function setCount(id, value) {
        const element = document.getElementById(id);

        if (!element) {
            console.error('Element not found:', id);
            return;
        }

        const count = parseInt(value, 10) || 0;

        element.textContent = count;

        /*
         * Zero hone par individual badge hide karna ho to
         * neeche wali line enable kar sakte hain.
         */

        // element.style.display = count > 0 ? 'inline-block' : 'none';
    }


    /*
     * Bell ke upar total notification badge update karta hai.
     */
    function updateTotalBadge() {
        const pendingListingCount = getCount('pendingListingCount');
        const newEnquiryCount = getCount('newEnquiryCount');
        const guestChatCount = getCount('guestChatCount');
        const updateListingCount = getCount('updateListingCount');

        const total =
            pendingListingCount +
            newEnquiryCount +
            guestChatCount +
            updateListingCount;

        const badge = document.getElementById('adminNotifyBadge');

        if (!badge) {
            console.error('adminNotifyBadge not found');
            return;
        }

        badge.textContent = total;

        if (total > 0) {
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }

        console.log('Notification counts:', {
            listings: pendingListingCount,
            enquiries: newEnquiryCount,
            chats: guestChatCount,
            updateListings: updateListingCount,
            total: total
        });
    }


    /*
     * Notification audio continuously play karta hai.
     */
  function startNotificationSound(type) {
    const audio = document.getElementById('adminNotifySound');

    if (!audio) {
        console.error('Notification audio element not found');
        return;
    }

    if (type === 'listing') {
        audio.src = "{{ asset('assets/sounds/listing.mp3') }}";
    } else {
        audio.src = "{{ asset('assets/sounds/enqury.mp3') }}";
    }

    audio.loop = true;
    audio.currentTime = 0;

    audio.play().catch(function (error) {
        console.warn(
            'Browser ne audio block kiya. Page par ek baar click karna zaroori ho sakta hai:',
            error
        );
    });
}


    /*
     * Notification audio stop karta hai.
     */
    function stopNotificationSound() {
        const audio = document.getElementById('adminNotifySound');

        if (!audio) {
            return;
        }

        audio.pause();
        audio.currentTime = 0;
    }


    /*
     * Notification type ke hisaab se popup data banata hai.
     */
    function getNotificationDetails(data) {

        switch (data.type) {

            case 'listing':
                return {
                    title: 'New Listing Received',
                    message: data.message ||
                        'A new listing has been submitted and is waiting for approval.',
                    icon: 'info'
                };

            case 'enquiry':
                return {
                    title: 'New Enquiry Received',
                    message: data.message ||
                        'A new customer enquiry has been received.',
                    icon: 'warning'
                };

            case 'guest_chat':
                return {
                    title: 'New Guest Message',
                    message: data.message ||
                        'A guest has sent a new chat message.',
                    icon: 'info'
                };

            case 'update_listing':
                return {
                    title: 'Listing Update Received',
                    message: data.message ||
                        'A listing update request is waiting for approval.',
                    icon: 'warning'
                };

            default:
                return {
                    title: 'New Notification',
                    message: data.message ||
                        'A new admin notification has been received.',
                    icon: 'info'
                };
        }
    }


    /*
     * Event ke count ke basis par dropdown badge update karta hai.
     */
    function updateNotificationCount(data) {

        if (typeof data.count === 'undefined') {
            console.warn('Notification count missing:', data);
            return;
        }

        const count = parseInt(data.count, 10) || 0;

        switch (data.type) {

            case 'listing':
                setCount('pendingListingCount', count);
                break;

            case 'enquiry':
                setCount('newEnquiryCount', count);
                break;

            case 'guest_chat':
                setCount('guestChatCount', count);
                break;

            case 'update_listing':
                setCount('updateListingCount', count);
                break;

            default:
                console.warn('Unknown notification type:', data.type);
                return;
        }

        updateTotalBadge();
    }


    /*
     * Queue ki next notification SweetAlert mein show karta hai.
     */
    function showNextNotification() {

        if (notificationPopupActive) {
            return;
        }

        if (notificationQueue.length === 0) {
            return;
        }

        notificationPopupActive = true;

        const data = notificationQueue.shift();
        const notification = getNotificationDetails(data);

       startNotificationSound(data.type);

        Swal.fire({
            title: notification.title,
            text: notification.message,
            icon: notification.icon,

            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',

            showConfirmButton: true,

            /*
             * Admin OK dabaye bina popup close nahi kar sakta.
             */
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: true,

            /*
             * Popup auto-close nahi hoga.
             */
            timer: undefined,
            timerProgressBar: false,

          didOpen: function () {
    startNotificationSound(data.type);
},
            willClose: function () {
                stopNotificationSound();
            }

        }).then(function () {

            stopNotificationSound();

            notificationPopupActive = false;

            /*
             * Agar aur notifications queue mein hain
             * to next popup show hoga.
             */
            showNextNotification();
        });
    }


    /*
     * New notification queue mein add karta hai.
     */
    function addNotificationToQueue(data) {
        notificationQueue.push(data);
        showNextNotification();
    }


    /*
     * Initial page-load count.
     */
    updateTotalBadge();


    /*
     * Echo loaded hai ya nahi.
     */
    if (typeof window.Echo === 'undefined') {
        console.error('Laravel Echo not loaded');
        return;
    }


    /*
     * Live admin notification channel.
     */
    window.Echo
        .channel('admin-notifications')
        .listen('.admin.notification', function (event) {

            console.log('FULL EVENT OBJECT:', event);
            console.log('EVENT JSON:', JSON.stringify(event));

            /*
             * Direct aur nested event formats dono handle karega.
             */
            const data =
                event.data ||
                event.notification ||
                event;

            console.log('FINAL EVENT DATA:', data);
            console.log('EVENT TYPE:', data.type);
            console.log('EVENT COUNT:', data.count);
            console.log('EVENT MESSAGE:', data.message);

            if (!data.type) {
                console.error('Notification type missing:', data);
                return;
            }

            /*
             * Badge count update.
             */
            updateNotificationCount(data);

            /*
             * SweetAlert live notification show.
             */
            addNotificationToQueue(data);
        });


    /*
     * Pusher connection debug logs.
     */
    if (
        window.Echo.connector &&
        window.Echo.connector.pusher &&
        window.Echo.connector.pusher.connection
    ) {

        window.Echo.connector.pusher.connection.bind(
            'connected',
            function () {
                console.log('Pusher connected successfully');
            }
        );

        window.Echo.connector.pusher.connection.bind(
            'error',
            function (error) {
                console.error('Pusher connection error:', error);
            }
        );
    }

});
</script>