/**
 * Report Submission JavaScript
 * FixIt - Community Infrastructure Reports
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Map Initialization
    const defaultLat = 14.5995; // Manila coordinates as fallback
    const defaultLng = 120.9842;
    const map = L.map('report-map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address');
    const addressDisplay = document.getElementById('address-display');

    // 2. Geolocation API
    const detectLocation = () => {
        if (!navigator.geolocation) {
           addressDisplay.innerHTML = '<span class="text-danger">Geolocation is not supported by your browser.</span>';
           return;
        }

        addressDisplay.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Locating...';
        
        const geoOptions = {
            enableHighAccuracy: true,
            timeout: 10000, // 10 seconds
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                updateLocation(latitude, longitude);
                map.setView([latitude, longitude], 16);
            },
            (error) => {
                let msg = "Unable to retrieve your location.";
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg = "Location permission denied. Please allow location access in your browser settings.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        msg = "Location request timed out. Please try again or manually pick on the map.";
                        break;
                }
                addressDisplay.innerHTML = `<span class="text-danger small"><i data-lucide="alert-circle" style="width:14px"></i> ${msg}</span>`;
                if (window.lucide) window.lucide.createIcons();
                console.error("Geolocation error:", error);
            },
            geoOptions
        );
    };

    // 3. Update marker and fetch address
    const updateLocation = (lat, lng) => {
        marker.setLatLng([lat, lng]);
        latInput.value = lat;
        lngInput.value = lng;

        // Reverse Geocoding via Nominatim
        addressDisplay.innerText = "Fetching address...";
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
            .then(response => response.json())
            .then(data => {
                const address = data.display_name || "Unknown address";
                addressDisplay.innerText = address;
                addressInput.value = address;
            })
            .catch(err => {
                console.error("Reverse geocoding error:", err);
                addressDisplay.innerText = "Address capture failed. You can still submit.";
                addressInput.value = `Captured Lat/Lng: ${lat}, ${lng}`;
            });
    };

    // Auto-detect location on load
    detectLocation();

    // Move marker on map click
    map.on('click', (e) => {
        updateLocation(e.latlng.lat, e.latlng.lng);
    });

    // Update on marker drag end
    marker.on('dragend', () => {
        const position = marker.getLatLng();
        updateLocation(position.lat, position.lng);
    });

    // Button trigger
    document.getElementById('btn-detect-location').addEventListener('click', detectLocation);

    // 4. Image Upload Previews
    const uploadZone = document.getElementById('upload-zone');
    const imageInput = document.getElementById('image-input');
    const previewsContainer = document.getElementById('image-previews');

    uploadZone.addEventListener('click', () => imageInput.click());

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            handlePreviews(e.dataTransfer.files);
        }
    });

    imageInput.addEventListener('change', () => {
        handlePreviews(imageInput.files);
    });

    const handlePreviews = (files) => {
        previewsContainer.innerHTML = '';
        if (files.length > 0) {
            previewsContainer.classList.remove('d-none');
        } else {
            previewsContainer.classList.add('d-none');
        }

        // Limit to 3 files
        const count = Math.min(files.length, 3);
        
        for (let i = 0; i < count; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const col = document.createElement('div');
                    col.className = 'col-4';
                    col.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded-3 border" style="height: 80px; width: 100%; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-1">
                                <span class="badge bg-dark rounded-pill" style="opacity: 0.8">${(file.size / (1024 * 1024)).toFixed(1)}MB</span>
                            </div>
                        </div>
                    `;
                    previewsContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        }
    };
});
