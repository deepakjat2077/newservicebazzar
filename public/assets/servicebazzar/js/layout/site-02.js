
window.showLocationPermissionPopup = function(options){
    options = options || {};

    const popup = document.getElementById('locationPermissionPopup');
    const message = document.getElementById('locationPermissionPopupMessage');
    const steps = document.getElementById('locationPermissionPopupSteps');
    const retry = document.getElementById('locationPermissionPopupRetry');

    if(!popup || !message || !steps || !retry) return;

    message.textContent = options.message || 'Please allow location access to detect your city.';
    steps.hidden = !options.blocked;
    retry.textContent = options.retryText || (options.blocked ? 'Try again' : 'Turn on location');
    popup.hidden = false;

    retry.onclick = function(){
        popup.hidden = true;
        if(typeof options.onRetry === 'function') options.onRetry();
    };
};

document.addEventListener('click', function(event){
    if(event.target.matches('[data-location-popup-close]')){
        const popup = document.getElementById('locationPermissionPopup');
        if(popup) popup.hidden = true;
    }
});
