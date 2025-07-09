@extends('template.scan')
@section('content')

<title>Scan In - {{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</title>

<style>
    .jamanalog-frame #analogclock {
        font-size: 10px;
        --size: 50em;
        margin: 0 auto;
        padding: 0;
        width: var(--size);
        height: var(--size);
        border: 2em solid #123;
        border-radius: 50%;
        display: block;
        position: relative;
        text-align: center;
        background: #BBD131;
        box-shadow: inset 0 0 0.8em 0.8em #ffe,
            0 0 0 0.5em #def,
            0 0 0 0.7em #456;
    }

    .jamanalog-frame .minmarks {
        display: inline-block;
        position: relative;
        width: 100%;
        height: 100%;
        top: 0;
        border: 0.2em solid #000;
        border-radius: 50%;
        box-shadow: 0 0 0.5em 0.5em #89a;
    }

    .jamanalog-frame #brand {
        display: inline-block;
        position: absolute;
        width: 100%;
        height: auto;
        top: 55%;
        left: 0;
        text-align: center;
        font-family: "Great Vibes", cursive;
        font-size: 300%;
        color: #345;
    }

    .jamanalog-frame #clocknumbers,
    .jamanalog-frame #clocknumbers24 {
        display: inline-block;
        position: absolute;
        border: 0 none;
        border-radius: 50%;
    }

    .jamanalog-frame #clocknumbers {
        width: 84%;
        height: 84%;
        top: 8%;
        left: 8%;
    }

    .jamanalog-frame #clocknumbers24 {
        width: 72%;
        height: 72%;
        top: 14%;
        left: 14%;
        border: 0 none;
    }

    .jamanalog-frame .clockdigits,
    .jamanalog-frame .clockdigits24 {
        position: absolute;
        padding: 0;
        margin: 0;
        width: 4em;
        height: 1em;
        text-align: center;
        font-family: "Times New Roman", serif;
        font-weight: bold;
    }

    .jamanalog-frame .clockdigits {
        font-size: 400%;
        color: #345;
    }

    .jamanalog-frame .clockdigits24 {
        color: #666;
        font-size: 100%;
    }

    .jamanalog-frame #nut {
        display: inline-block;
        position: absolute;
        width: 2.4em;
        height: 2.4em;
        top: calc(50% - 1.2em);
        left: calc(50% - 1.2em);
        border: 0.2em solid #333;
        background: radial-gradient(#ccc, #000, #ccc);
        border-radius: 50%;
        box-shadow: inset 0 0 0 0.1em #333,
            0 0 0 0.1em #000;
    }

    .jamanalog-frame .guide {
        position: absolute;
        background: transparent;
        height: 100%;
        width: 1px;
        left: 50%;
        top: 0;
        transform: rotate(var(--angle));
        font-family: "Times New Roman", serif;
        font-weight: bold;
        font-size: 1em;
    }

    .jamanalog-frame .guide::before {
        content: '';
        width: 0.5em;
        height: 1em;
        background: #000;
        position: absolute;
        left: 0;
        top: 0;
    }

    .jamanalog-frame .guide::after {
        content: '';
        width: 0.5em;
        height: 1em;
        background: #000;
        position: absolute;
        left: 0;
        top: calc(100% - 1em);
    }

    .jamanalog-frame .guidedot {
        position: absolute;
        background: transparent;
        height: 100%;
        width: 1px;
        left: 50%;
        top: 0;
        transform: rotate(var(--angle));
    }

    .jamanalog-frame .guidedot::before {
        content: '';
        width: 0.25em;
        height: 0.5em;
        background: #000;
        position: absolute;
        left: 0;
        top: 0;
    }

    .jamanalog-frame .guidedot::after {
        content: '';
        width: 0.25em;
        height: 0.5em;
        background: #000;
        position: absolute;
        left: 0;
        top: calc(100% - 0.5em);
    }

    .jamanalog-frame #secondscircle {
        position: absolute;
        width: 94%;
        height: 94%;
        left: 3%;
        top: 3%;
        border-radius: 50%;
    }

    .jamanalog-frame #secondshand {
        position: absolute;
        background: #c00;
        width: 0.2em;
        height: 50%;
        left: 50%;
        top: 0;
        transform-origin: bottom center;
        box-shadow: 0 0 0.3em 0.1em #ccc,
            0 4em 0 0 #c00;
    }

    .jamanalog-frame #secondshand:after {
        position: absolute;
        content: '';
        width: 1em;
        height: 3em;
        background: linear-gradient(to right, #fcc, #c00, #fcc);
        left: -0.4em;
        top: calc(100% + 2em);
        border-radius: 0.5em;
        box-shadow: 0 0 1em 0.1em #ccc;
    }

    .jamanalog-frame #minutescircle {
        position: absolute;
        width: 74%;
        height: 74%;
        /*   border:1px solid green; */
        left: 13%;
        top: 13%;
        border-radius: 50%;
    }

    .jamanalog-frame #minuteshand {
        position: absolute;
        width: 0.8em;
        left: calc(50% - 0.4em);
        background: linear-gradient(#345 0%, #234 100%);
        box-shadow: 0 0 0.8em 0.1em #999;
    }

    .jamanalog-frame #hourscircle {
        position: absolute;
        width: 50%;
        height: 50%;
        border: 0 none;
        left: 25%;
        top: 25%;
        border-radius: 50%;
    }

    .jamanalog-frame #hourscircle,
    .jamanalog-frame #minutescircle,
    .jamanalog-frame #secondscircle {
        pointer-events: none;
    }

    .jamanalog-frame #hourshand {
        position: absolute;
        width: 1.2em;
        left: calc(50% - 0.6em);
        top: 0;
        background: linear-gradient(#234 0%, #123 100%);
        box-shadow: 0 0 1.2em 0.1em #999;
    }

    .jamanalog-frame #minuteshand,
    .jamanalog-frame #hourshand {
        top: 0;
        height: 50%;
        border-radius: 50% 50% 0 0;
        transform-origin: bottom center;
        pointer-events: none;
    }


    .jamanalog-frame #digitalclock {
        text-align: center;
        margin: 40px auto 20px auto;
        font-family: 'Roboto Mono', monaco, monospace;
        font-size: 4rem;
        color: #0f0;
        padding: 0.75em;
        background: #000;
        width: 10em;
        border: 4px solid silver;
        border-radius: 8px;
        box-shadow: inset 0 0 0 0.5em #234;
    }

    .jamanalog-frame .comment {
        margin: 2rem auto 2rem auto;
        width: 80%;
        text-align: center;
    }


    .jamanalog-frame #soundtoggle {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 0 none;
        border-radius: 0;
        background-color: transparent;
        position: absolute;
        left: 47%;
        top: 75%;
        cursor: pointer;
    }

    .jamanalog-frame .speaker {
        --color: #000;
        --size: 3rem;
        width: var(--size);
        height: var(--size);
        position: relative;
        background: transparent;
        display: inline-block;
        opacity: 0.9;
    }

    .jamanalog-frame .speaker:hover {
        opacity: 1;
    }

    .jamanalog-frame .speakertriangle {
        display: inline-block;
        width: 0;
        height: 0;
        border-top: calc(var(--size) * 0.4) solid transparent;
        border-bottom: calc(var(--size) * 0.4) solid transparent;
        border-right: calc(var(--size) * 0.5) solid var(--color);
        position: absolute;
        left: calc(var(--size) * 0.05);
        top: calc(var(--size) * 0.1);
    }

    .jamanalog-frame .speakerbar {
        display: inline-block;
        width: calc(var(--size) * 0.333);
        height: calc(var(--size) * 0.333);
        background: var(--color);
        position: absolute;
        left: 0;
        top: calc(var(--size) * 0.333);
    }

    .jamanalog-frame .speaker--off {
        opacity: 0.6;
    }

    .jamanalog-frame .speaker--off .speakertriangle {
        left: calc(var(--size) * 0.25);
    }

    .jamanalog-frame .speaker--off .speakerbar {
        left: calc(var(--size) * 0.2)
    }

    .jamanalog-frame .speakerwave {
        display: inline-block;
        border: 2px solid var(--color);
        border-left-color: transparent;
        border-top-color: transparent;
        border-bottom-color: transparent;
        border-radius: 50%;
        position: absolute;
    }

    .jamanalog-frame .speaker--off .speakerwave {
        display: none;
    }

    .jamanalog-frame .speakerwave--1 {
        width: calc(var(--size) * 0.35);
        height: calc(var(--size) * 0.5);
        right: calc(var(--size) * 0.3);
        top: calc(var(--size) * 0.25);
        opacity: 0.4;
    }

    .jamanalog-frame .speakerwave--2 {
        width: calc(var(--size) * 0.5);
        height: calc(var(--size) * 0.8);
        right: calc(var(--size) * 0.15);
        top: calc(var(--size) * 0.1);
        opacity: 0.6;
    }

    .jamanalog-frame .speakerwave--3 {
        width: calc(var(--size) * 0.5);
        height: calc(var(--size) * 1);
        right: calc(var(--size) * 0);
        top: calc(var(--size) * 0);
        opacity: 0.8;
    }
</style>

<script>
    let analogclock, digitalclock, secondshand, minuteshand, hourshand, soundtoggle, context;
    let tickURL = 'https://teknologi.visitklaten.com/wp-content/uploads/2022/07/ticksingle.wav';
    let audioinfo = {
        "url": tickURL,
        "buffer": null
    };
    let now, h, m, s, timerid;
    let ticking = false;

    document.addEventListener('DOMContentLoaded', function(e) {
        try {
            init();
        } catch (error) {
            console.log("Data didn't load", error);
        }
    });

    function init() {
        secondshand = gid("secondshand");
        minuteshand = gid("minuteshand");
        hourshand = gid("hourshand");
        analogclock = gid("analogclock");
        digitalclock = gid("digitalclock");
        soundtoggle = gid("soundtoggle");
        makeMinMarkers();
        makeClockNumbers();
        //timerid = requestAnimationFrame(update);
        timerid = setInterval(update, 1000);
        soundtoggle.addEventListener("click", toggleTick, false);
    }

    function getAudio(audioObject, callback) {
        context = new(window.AudioContext || window.webkitAudioContext)();
        let req = new XMLHttpRequest();
        req.audioObject = audioObject;
        req.open("GET", audioObject.url, true);
        req.responseType = "arraybuffer";
        req.addEventListener("progress", updateAudioProgress, false);
        req.onload = function() {
            //decode the loaded data 
            context.decodeAudioData(req.response, function(buffer) {
                audioObject.buffer = buffer;
                if (callback) {
                    callback(audioObject);
                }
            });
        };
        req.send();
    }

    function updateAudioProgress(oEvent) {
        if (oEvent.lengthComputable) {
            let pc = oEvent.loaded / oEvent.total;
            //console.log(event.target);
            //console.log("Percent loaded is " + pc + "%.");
            if (pc >= 1) {
                //console.log("Track loaded.");
            }
        }
    }

    function tickLoadChecker(audioObject) {
        console.log("tick audio loaded from " + audioObject.url);
    }

    function update() {
        now = new Date();
        let oldsecond = s;
        h = now.getHours();
        m = now.getMinutes();
        s = now.getSeconds();
        secondshand.style.transform = `rotate(${s * 6}deg)`;
        minuteshand.style.transform = `rotate(${m * 6}deg)`;
        hourshand.style.transform = `rotate(${(h * 30) + ((m/60) * 30)}deg)`;
        digitalclock.innerHTML = now.toLocaleTimeString();
        //timerid = requestAnimationFrame(update);
        if (ticking) {
            if (oldsecond !== s) {
                if (audioinfo.buffer) {
                    playTick(audioinfo.buffer);
                }
            }
        }
    }

    function toggleTick() {
        if (!audioinfo.buffer) {
            getAudio(audioinfo, tickLoadChecker);
        }
        ticking = !ticking;
        if (ticking) {
            playTick(audioinfo.buffer);
            soundtoggle.classList.remove("speaker--off");
            soundtoggle.classList.add("speaker--on");
        } else {
            soundtoggle.classList.remove("speaker--on");
            soundtoggle.classList.add("speaker--off");
        }
    }

    function playTick(audioBuffer) {
        if (context.state === "suspended") {
            context.resume();
        }
        let source = context.createBufferSource();
        source.buffer = audioBuffer;
        source.connect(context.destination);
        source.start();
    }

    function gid(idstring) {
        //saves lots of typing for those who eschew Jquery
        return document.getElementById(idstring);
    }

    function makeMinMarkers() {
        let minmarkers = gid("minmarkers");
        let code = "";
        for (let i = 0; i < 30; i++) {
            let classname = i % 5 === 0 ? "guide" : "guidedot";
            let angle = i * 6;
            code += `<div class="${classname}" style="--angle:${angle}deg;"></div>`;
        }
        minmarkers.innerHTML = code;
    }

    function makeClockNumbers() {
        let clocknumbers = gid("clocknumbers");
        let clocknumbers24 = gid("clocknumbers24");
        let nums = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2];
        let code = "";
        for (let i = 0; i < 12; i++) {
            code += `<div class="clockdigits">${nums[i]}</div>`;
        }
        clocknumbers.innerHTML = code;
        code = ``;
        for (let i = 0; i < 12; i++) {
            let mil = (nums[i] + 12).toString();
            code += `<div class="clockdigits24">${mil}</div>`;
        }
        clocknumbers24.innerHTML = code;
        let cns = document.querySelectorAll(".clockdigits");
        for (let i = 0; i < 12; i++) {
            let p = getCirclePoint(50, (i * 30), 50, 50);
            cns[i].style.left = `calc(${p.x}% - 2em)`;
            cns[i].style.top = `calc(${p.y}% - 0.75em)`;
        }
        let cns24 = document.querySelectorAll(".clockdigits24");
        for (let i = 0; i < 12; i++) {
            let p = getCirclePoint(50, (i * 30), 50, 50);
            cns24[i].style.left = `calc(${p.x}% - 2em)`;
            cns24[i].style.top = `calc(${p.y}% - 0.75em)`;
        }
    }

    function getCirclePoint(r, degrees, cx, cy) {
        let angleInRadians = degrees * (Math.PI / 180);
        let x = cx + r * Math.cos(angleInRadians);
        let y = cy + r * Math.sin(angleInRadians);
        return {
            x,
            y
        };
    }
</script>

<div class="container pt-5">
    <img src="https://aigis-moi.id/dont_delete/top_logo_with_aigis.png" alt="kemenperin" class="img-fluid" style="width: 1200px; display: block; margin: auto;">
    <div class="form-group mt-3">
        <h2 class="text-light text-center" style="color: #006831;">CHECK-IN</h2>
        <div class="input-group">
            <div class="input-group-prepend camera-on" style="cursor: pointer">
                <div class="input-group-text">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <input id="qrcode" type="text" class="form-control" autofocus autocomplete="off">
        </div>

        <div id="open-camera" class="row justify-content-center mt-4 d-none">
            <div class="col-lg-6">
                <div class="card-body d-flex justify-content-center">
                    <div class="p-1 rounded" style="background-color: #6c3c0c">
                        <div id="my-camera"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="jamanalog-frame">
                    <div id="analogclock" style="transform: scale(0.8) !important; height: 400px !important; width: 400px !important;">
                        <div id="minmarkers" class="minmarks"></div>
                        <div id="clocknumbers"></div>
                        <div id="clocknumbers24"></div>
                        <div id="brand"><img src="https://aigis-moi.id/dont_delete/agis_2.png" style="height: 100px !important; width: auto !important;" alt=""></div>
                        <!-- <button type="button" id="soundtoggle" class="speaker speaker--off" aria-label="sound toggle"> -->
                        <span class="speakertriangle"></span>
                        <span class="speakerbar"></span>
                        <span class="speakerwave speakerwave--1"></span>
                        <span class="speakerwave speakerwave--2"></span>
                        <span class="speakerwave speakerwave--3"></span>
                        </button>
                        <div id="hourscircle">
                            <div id="hourshand"></div>
                        </div>
                        <div id="minutescircle">
                            <div id="minuteshand"></div>
                        </div>
                        <div id="secondscircle">
                            <div id="secondshand"></div>
                        </div>
                        <div id="nut"></div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="p-4 bg-light border" style="height: 400px;">
                    One of three columns
                </div>
            </div>
        </div>




    </div>
</div>
<script type="text/javascript" src="{{ asset('plugin/webcamjs/webcam.min.js') }}"></script>
<script>
    $(document).ready(function() {

        function customAlert(data) {
            if (data.status == "success") {
                Swal.fire({
                    title: "Scan Berhasil",
                    text: data.message,
                    icon: "success",
                    confirmButtonColor: "#6F4E37",
                });
            } else if (data.status == "warning") {
                Swal.fire({
                    title: "Peringatan",
                    text: data.message,
                    icon: "warning",
                    confirmButtonColor: "#6F4E37",
                });
            } else {
                Swal.fire({
                    title: "Gagal",
                    text: data.message,
                    icon: "error",
                    confirmButtonColor: "#6F4E37",
                });
            }

            $('#qrcode').val('');
            cameraOff();
        }

        function cameraOn() {
            $("#open-camera").removeClass('d-none');
            $("#open-camera").addClass('on-cam');
            Webcam.set({
                width: 500,
                height: 375,
                image_format: 'jpeg',
                jpeg_quality: 90,
                flip_horiz: true
            });
            Webcam.attach('#my-camera');
        }

        function cameraOff() {
            $("#open-camera").removeClass('on-cam');
            $("#open-camera").addClass('d-none');
            Webcam.reset();
        }

        $(".camera-on").click(function() {
            var camera = $("#open-camera").hasClass('on-cam');
            camera ? cameraOff() : cameraOn();
        })

        $('#qrcode').on("keypress", function(e) {
            if (e.keyCode == 13) {

                var qrcode = document.getElementById("qrcode").value;
                var url = "{{ url('scan/in-process') }}";
                var params = "?_token={{ csrf_token() }}&qrcode=" + qrcode;

                if ($("#open-camera").hasClass('on-cam')) {
                    Webcam.snap(function(data_uri) {

                        fullUrl = url + params;

                        Webcam.upload(data_uri, fullUrl, function(status, res) {
                            const data = JSON.parse(res);
                            customAlert(data)
                        });
                    });

                } else {
                    $.ajax({
                        url: url,
                        method: "POST",
                        type: "JSON",
                        data: {
                            _token: "{{ csrf_token() }}",
                            qrcode: qrcode,
                        },
                        success: (res) => {
                            customAlert(res)
                        },
                        error: (err) => {
                            customAlert({
                                status: "error",
                                message: "Scan gagal"
                            })
                        }
                    })

                }

            }
        });

    })
</script>
@endsection