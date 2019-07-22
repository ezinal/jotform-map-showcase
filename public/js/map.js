// Multiple Markers
var markers = [
    // ['London Eye, London', 51.503454, -0.119562],
];

// Info Window Content
var infoWindowContent = [
    // ['<div class="info_content">' +
    //     '<h3>London Eye</h3>' +
    //     '<p>The London Eye is a giant Ferris wheel situated on the banks of the River Thames.],
];
var ids = [];
var names = [];
var submissionInfos = [];

jQuery(function($) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: '/map',
        data: {
            tempo: 'tempo'
        },
        dataType: 'json',
        success: function(data) {
            // console.log("success sending and receiving data", data);
            $('#results').html(data);

            // console.log("length is: ", data.submissions.length);
            for (let i = 0; i < data.submissions.length; i++) {
                let id = data.submissions[i].id;
                let lat = data.submissions[i].lat;
                // console.log('gelen lat ', i, ': ', lat);
                let long = data.submissions[i].long;
                // console.log('gelen long ', i, ': ', long);
                let name = data.submissions[i].name;
                // console.log('gelen isim ', i, ': ', name);
                let full_address = data.submissions[i].full;
                // console.log('gelen full ', i, ': ', full_address);
                ids.push(id);
                markers.push([name, lat, long]);
                // console.log(i, markers);
                infoWindowContent.push(['<div class="info_content">' +
                    '<h3>' + name + ' is here</h3>' +
                    '<p>His/Her answers are shown</p>' + '</div>'
                ], );
            }
            // console.log('ids are: ', ids);
            loadMap();
        },
        error: function(data) {
            var errors = data.responseText;
            console.log(errors);
        }
    });
});

function loadMap() {
    // console.log(markers.toString());
    // console.log(infoWindowContent.toString());
    // Asynchronously Load the map API 
    var script = document.createElement('script');
    script.src = "//maps.googleapis.com/maps/api/js?callback=initialize"; //key=AIzaSyBUGNsYPactftMArGN8pviLvaugZO4OF6c&
    document.body.appendChild(script);
    $('#sidebar').toggleClass('active');
}

function initialize() {
    var map;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };

    // Display a map on the page
    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    map.setTilt(45);

    // Display multiple markers on a map
    var infoWindow = new google.maps.InfoWindow(),
        marker, i;
    var infoBox = $("#submission_text");

    // Loop through our array of markers & place each one on the map  
    for (i = 0; i < markers.length; i++) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });

        // Allow each marker to have an info window    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
                // $('#sidebarCollapse').on('click', function() {
                if ($('#sidebar').hasClass('active')) {
                    $('#sidebar').toggleClass('active');
                } else {}
                // infoBox.html(submissionInfos[i] + "<br/>");
                let url = 'https://www.jotform.com/inbox/' + ids[i];
                let iframe = '<iframe src=' + url + ' height=500 width=400> < /iframe>';

                // $('button').on('click', function(e) {
                // e.preventDefault();
                // $("#submission_info").empty();
                if ($("#submission_info iframe").length) {
                    $("#submission_info iframe").attr('src', url);
                } else {
                    $("#submission_info").append(iframe);
                }
                // });
            }
        })(marker, i));

        // Automatically center the map fitting all markers on the screen
        map.fitBounds(bounds);
    }

    // Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
    var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
        this.setZoom(3);
        google.maps.event.removeListener(boundsListener);

    });
}