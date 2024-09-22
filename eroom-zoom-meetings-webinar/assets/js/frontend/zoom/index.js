window.addEventListener('DOMContentLoaded', function(event) {
    websdkready();
});
function websdkready() {
    var testTool = window.testTool;
    if (testTool.isMobileDevice()) {
        vConsole = new VConsole();
    }
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();
    // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
    // if (!china) ZoomMtg.setZoomJSLib('https://source.zoom.us/2.8.0/lib', '/av'); // CDN version default
    // else ZoomMtg.setZoomJSLib('https://jssdk.zoomus.cn/2.8.0/lib', '/av'); // china cdn option
    // ZoomMtg.setZoomJSLib('http://localhost:9999/node_modules/@zoomus/websdk/dist/lib', '/av'); // Local version default, Angular Project change to use cdn version
    ZoomMtg.preLoadWasm(); // pre download wasm file to save time.
    /**
     * NEVER PUT YOUR ACTUAL SDK SECRET IN CLIENT SIDE CODE, THIS IS JUST FOR QUICK PROTOTYPING
     * The below generateSignature should be done server side as not to expose your SDK SECRET in public
     * You can find an eaxmple in here: https://marketplace.zoom.us/docs/sdk/native-sdks/web/essential/signature
     */
    // some help code, remember mn, pwd, lang to cookie, and autofill.
    document.getElementById("display_name").value =
        "CDN" +
        ZoomMtg.getJSSDKVersion()[0] +
        testTool.detectOS() +
        "#" +
        testTool.getBrowserInfo();
    document.getElementById("meeting_number").value = testTool.getCookie(
        "meeting_number"
    );
    document.getElementById("meeting_pwd").value = testTool.getCookie(
        "meeting_pwd"
    );
    if (testTool.getCookie("meeting_lang"))
        document.getElementById("meeting_lang").value = testTool.getCookie(
            "meeting_lang"
        );
}
