
var lat;
var lng;
var map;
var currentText = "Vị trí của bạn";
var infowindow;
var markerDynamic;
var markerCurrent;
var markerSearch;
var markerSearchDynamic;

$(document).ready(function(){
    $("#menu-toggle").click(function(e) {
        $('#sidebar').addClass('active');
        $('.overlay').addClass('active');
        $('.collapse.in').toggleClass('in');
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
    });

    $("#sidebar").mCustomScrollbar({
        theme: "minimal"
    });

    $('#dismiss, .overlay').on('click', function () {
        $('#sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

});


function initMap() {
    calculateWithHeightMap();

    map = new google.maps.Map(document.getElementById('google_map'), {
        // center: {lat: lat, lng: lng},
        zoom: 16,
        disableDefaultUI: true,
        styles: [
            {
                featureType: 'poi.business',
                stylers:  [
                    { visibility: "off" }
                ]
            },{
                featureType: 'poi.attraction',
                elementType: 'labels.text.fill',
                stylers: [{color: '#e28b0b'}]
            }
        ]
        // streetViewControl: true
    });

    markerDynamic = new google.maps.Marker({
        // position: {lat: lat, lng: lng},
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 0,
            strokeColor: 'white',
            strokeWeight: 1,
            fillColor: '#1A73E8',
            fillOpacity: 0.3
        },
    });

    markerCurrent = new google.maps.Marker({
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            strokeColor: 'white',
            strokeWeight: 2,
            fillColor: '#1A73E8',
            fillOpacity: 0.6
        },
        animation: google.maps.Animation.DROP
    });

    // Try HTML5 geolocation.


    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            lat = position.coords.latitude;
            lng=position.coords.longitude;

            var currentLocal = new google.maps.LatLng(lat,lng);
            var centerMap;

            if(p_lat>0 & p_lng>0 & p_if>0){
                centerMap = new google.maps.LatLng(p_lat,p_lng);
                showHotelInfo(p_if);
                loadMarkets(p_lat,p_lng);
            }else{
                centerMap = currentLocal;
                loadMarkets(lat,lng);
                markerCurrent.setPosition(currentLocal);
                markerDynamic.setPosition(currentLocal);
            }
            map.setCenter(centerMap);


        }, function() {
            map.setCenter(new google.maps.LatLng(20.994797,105.817385));
            handleLocationError(true, infowindow, map.getCenter());
        });
    } else {
        // Browser doesn't support Geolocation
        map.setCenter(new google.maps.LatLng(20.994797,105.817385));
        handleLocationError(false, infowindow, map.getCenter());
    }

    markerCurrent.addListener('click', function() {
        infowindow.open(map, markerCurrent);
    });

    infowindow = new google.maps.InfoWindow({
        content:currentText
    });
    infowindow.open(map,markerCurrent);
    
    initZoomControl(map);
    initMapTypeControl(map);
    initFullscreenControl(map);
    searchAddressUi();
    currentDynamic();

    google.maps.event.addListener(map, 'dragend', function() {
        lat = map.getCenter().lat();
        lng = map.getCenter().lng();
        currentDynamic();
        loadMarkets(lat,lng);
    } );
}

function calculateAndDisplayRoute(startPosition,endPosition) {
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);
    $('#directionsText').html('');
    directionsService.route(
        {
            origin: startPosition,
            destination: endPosition,
            travelMode: 'DRIVING'
        },
        function(response, status) {
            var msg;
            if (status === 'OK') {
                // directionsRenderer.setDirections(response);
                var totalDistance = 0;
                var totalDuration = 0;
                var legs = response.routes[0].legs;
                for(var i=0; i<legs.length; ++i) {
                    totalDistance += legs[i].distance.value;
                    totalDuration += legs[i].duration.value;
                }
                 msg = 'Chỗ ở cách vị trí bạn tìm {distance} km, {duration} phút lái xe';
                msg=msg.replace('{distance}', parseFloat(totalDistance/1000).toFixed(1));
                msg=msg.replace('{duration}', Math.round(totalDuration/60));

                msg = '<svg viewBox="0 0 294.843 294.843" class="icon"><use xlink:href="#compass"></use></svg> '+msg

            } else {
                //window.alert('Directions request failed due to ' + status);
                msg='';
            }
            $('#directionsText').html(msg);
        });
}

var dynamicIcon;
function currentDynamic() {
    // if(markerCurrent.getAnimation() !== google.maps.Animation.BOUNCE){
        var count=0;
        clearInterval(dynamicIcon);
        // markerCurrent.setAnimation(google.maps.Animation.BOUNCE);
        dynamicIcon= window.setInterval(function() {
            var icon = markerDynamic.get('icon');
            if(icon.scale<40){
                icon.scale = icon.scale+1;
            }else{
                count = count+1;
                icon.scale = 8;
                if(count==30){
                    // markerCurrent.setAnimation(null);
                    clearInterval(dynamicIcon);
                }
            }
            markerDynamic.set('icon', icon);
        }, 25);
    // }

}
var dynamicSearchIcon;
function searchDynamicIcon() {
    var count=0;
    clearInterval(dynamicSearchIcon);
    markerSearch.setAnimation(google.maps.Animation.BOUNCE);
    dynamicSearchIcon= window.setInterval(function() {
        var icon = markerSearchDynamic.get('icon');
        if(icon.scale<40){
            icon.scale = icon.scale+1;
            // markerSearchDynamic.set('icon', icon);
        }else{
            count = count+1;
            icon.scale = 0;
            if(count==30)
                markerSearch.setAnimation(null);
        }
        markerSearchDynamic.set('icon', icon);
    }, 25);
}

function searchAddressUi() {

    var searchInput = document.getElementById('mapSearchInput');

    var searchBox = new google.maps.places.SearchBox(searchInput);
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });
    var markers = [];
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        markers.forEach(function(marker) {
            marker.setMap(null);
        });
        markers = [];

        var bounds = new google.maps.LatLngBounds();

        places.forEach(function(place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }

            markerSearchDynamic = new google.maps.Marker({
                // position: markerSearch.getPosition(),
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    strokeColor: 'white',
                    strokeWeight: 1,
                    fillColor: '#1A73E8',
                    fillOpacity: 0.3
                },
            });

            markerSearch = new google.maps.Marker({
                // position: markerSearchDynamic.getPosition(),
                map: map,
                icon: {
                    url: '/img/search-marker.png',
                    scaledSize: new google.maps.Size(40, 40),
                },
                animation: google.maps.Animation.DROP,
                address:place.name
            });

            markerSearch.setPosition(place.geometry.location);
            markerSearchDynamic.setPosition(place.geometry.location);

            markers.push(markerSearch);
            markers.push(markerSearchDynamic);

            infowindow = new google.maps.InfoWindow({
                content:place.name
            });
            map.setCenter(place.geometry.location);
            infowindow.open(map, markerSearch);
            loadMarkets(place.geometry.location.lat(),place.geometry.location.lng());
            searchHistory(place);

            // if (place.geometry.viewport) {
            //     bounds.union(place.geometry.viewport);
            // } else {
            //     bounds.extend(place.geometry.location);
            // }
        });
        searchDynamicIcon();
        // map.fitBounds(bounds);
        // map.setCenter()
        map.setZoom(14);
    });
}

function searchHistory(place) {
    try {
        var componentSearch = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            administrative_area_level_1: 'short_name',
            country: 'long_name',
            postal_code: 'short_name'
        };

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentSearch[addressType]) {
                componentSearch[addressType]=place.address_components[i][componentSearch[addressType]];
            }
        }

        //console.log(searchHistory);
        $.ajax({
            method: "POST",
            url: "/history/add-map-search-history.html",
            data: {
                search: JSON.stringify(componentSearch)
            }
        });
    }catch(err) {

    }

}

function loadMarkets(p_lat,p_lng) {
    var url_markets = url+"/"+p_lat +"/"+p_lng;
    $.getJSON(url_markets, function(datas) {
        $.each(datas, function(key, data) {
            //Window info
            var srcImg = '/img/small-logo.png';
            if(data.default_img) srcImg = data.default_img;
            var promotion = '';
            if((data.promotion).length > 5) promotion=' <span class="badge badge-pill badge-success">'+data.promotion+'</span>';

            var windowContent = '<div class="card" style="min-width: 25rem; padding: 10px 5px 10px 10px" id="winfowInfo">\n' +
                '        <div class="row no-gutters">\n' +
                '            <div class="col-auto padding-right-15">\n' +
                '                <img style="max-height: 100px; max-width: 130px" src="'+srcImg+'" class="img-fluid" alt="">\n' +
                '            </div>'+
                '            <div class="col">' +
                '            <div class="card-block px-2">' +
                '               <h5 class="card-title" style="margin-bottom:5px">'+data.name+'</h5>\n' +
                '               <p class="card-text">' +
                '                   <svg viewBox="0 0 512 512" class="icon"><use xlink:href="#place"></use></svg> <span>'+data.address+'</span><br>'+
                '                   <svg viewBox="0 0 512 512" class="icon"><use xlink:href="#save-money"></use></svg> <span>'+data.price+'</span>'+
                promotion+
                '               </p>\n' +
                '            </div>'+
                '            </div>'+
                '        </div>\n' +
                '    </div>';

            var infowindow = new google.maps.InfoWindow({
                content: windowContent
            });

            var marketIcon = {
                url: '/img/icon-hotel.png',
                // This marker is 20 pixels wide by 32 pixels high.
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(40,40)
            };

            // console.log(data.name);
            var latLng = new google.maps.LatLng(data.lat, data.lng);
            // Creating a marker and putting it on the map
            var marker = new google.maps.Marker({
                id:data.id,
                position: latLng,
                title: data.name,
                icon: marketIcon,
                address:data.address
            });
            // infowindow.open(map,marker);
            marker.setMap(map);

            marker.addListener('click', marketClick);

            marker.addListener('mouseover', function() {
                infowindow.open(map, this);
            });

            // assuming you also want to hide the infowindow when user mouses-out
            marker.addListener('mouseout', function() {
                infowindow.close();
            });
        });
    });
}

function marketClick() {
    var currentMarket = this;
    var url = '/application/hotel-info/0/0/'+this.id;
    $.ajax({
        type: "GET",
        url: url,
        success: function(res) {
            $('.modal-content').html(res);
            $('#modalHotelInfo').modal('show');

            $('#modalHotelInfo').on('shown.bs.modal', function () {
                try {
                    calculateAndDisplayRoute(currentMarket.position,markerSearch.position);
                }catch (e) {

                }
            })
        },
        error:function(request, status, error) {
            //console.log("ajax call went wrong:" + request.responseText);
        }
    });
}

function showHotelInfo(id){
    var url = '/application/hotel-info/0/0/'+id;
    $.ajax({
        type: "GET",
        url: url,
        success: function(res) {
            $('.modal-content').html(res);
            $('#modalHotelInfo').modal('show');
        },
        error:function(request, status, error) {
            //console.log("ajax call went wrong:" + request.responseText);
        }
    });
}

function calculateWithHeightMap(){
    var navH = $('#navbar').innerHeight();
    // var mapW = $(window).width();
    var mapW = '100%';
    var mapH = $(window).height()-navH;
    $('#google_map').width(mapW).height(mapH);
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
        'Error: The Geolocation service failed.' :
        'Error: Your browser doesn\'t support geolocation.');
    infoWindow.open(map);
}

function initZoomControl(map) {
    document.querySelector('.zoom-control-in').onclick = function() {
        map.setZoom(map.getZoom() + 1);
    };
    document.querySelector('.zoom-control-out').onclick = function() {
        map.setZoom(map.getZoom() - 1);
    };
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(
        document.querySelector('.zoom-control'));
}

function initMapTypeControl(map) {
    var mapTypeControlDiv = document.querySelector('.maptype-control');
    document.querySelector('.maptype-control-map').onclick = function() {
        mapTypeControlDiv.classList.add('maptype-control-is-map');
        mapTypeControlDiv.classList.remove('maptype-control-is-satellite');
        map.setMapTypeId('roadmap');
    };
    document.querySelector('.maptype-control-satellite').onclick =
        function() {
            mapTypeControlDiv.classList.remove('maptype-control-is-map');
            mapTypeControlDiv.classList.add('maptype-control-is-satellite');
            map.setMapTypeId('hybrid');
        };

    map.controls[google.maps.ControlPosition.BOTTOM].push(
        mapTypeControlDiv);
}

function initFullscreenControl(map) {
    var elementToSendFullscreen = map.getDiv().firstChild;
    var fullscreenControl = document.querySelector('.fullscreen-control');
    map.controls[google.maps.ControlPosition.RIGHT_TOP].push(
        fullscreenControl);


    fullscreenControl.onclick = function() {
        if (isFullscreen(elementToSendFullscreen)) {
            exitFullscreen();
        } else {
            requestFullscreen(elementToSendFullscreen);
        }
    };

    document.onwebkitfullscreenchange =
        document.onmsfullscreenchange =
            document.onmozfullscreenchange =
                document.onfullscreenchange = function() {
                    if (isFullscreen(elementToSendFullscreen)) {
                        fullscreenControl.classList.add('is-fullscreen');
                    } else {
                        fullscreenControl.classList.remove('is-fullscreen');
                    }
                };
}

function isFullscreen(element) {
    return (document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement) == element;
}

function requestFullscreen(element) {
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.webkitRequestFullScreen) {
        element.webkitRequestFullScreen();
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
    } else if (element.msRequestFullScreen) {
        element.msRequestFullScreen();
    }
}

function exitFullscreen() {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.msCancelFullScreen) {
        document.msCancelFullScreen();
    }
}