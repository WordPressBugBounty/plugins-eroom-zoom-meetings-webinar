window.addEventListener(
	'DOMContentLoaded',
	function(event) {
		websdkready();
	}
);

function websdkready() {
	var testTool      = window.testTool;
	var meetingConfig = {
		apiKey: API_KEY,
		secretKey: SECRET_KEY,
		meetingNumber: meeting_id,
		userName: username,
		passWord: meeting_password,
		leaveUrl: leaveUrl,
		role: 0, //0-Attendee,1-Host,5-Assistant
		userEmail: email,
		lang: lang,
		signature: "",
		china: 0,//0-GLOBAL, 1-China
	};

	if (testTool.isMobileDevice()) {
		vConsole = new VConsole();
	}

	ZoomMtg.preLoadWasm();
	ZoomMtg.prepareJssdk();

	ZoomMtg.inMeetingServiceListener(
		'onUserJoin',
		function (data) {}
	);

	ZoomMtg.inMeetingServiceListener(
		'onUserLeave',
		function (data) {}
	);

	ZoomMtg.inMeetingServiceListener(
		'onUserIsInWaitingRoom',
		function (data) {}
	);

	ZoomMtg.inMeetingServiceListener(
		'onMeetingStatus',
		function (data) {}
	);

	ZoomMtg.preLoadWasm();
	ZoomMtg.prepareJssdk();
	function beginJoin() {
		var signature = ZoomMtg.generateSDKSignature(
			{
				meetingNumber: meetingConfig.meetingNumber,
				sdkKey: meetingConfig.apiKey,
				sdkSecret: meetingConfig.secretKey,
				role: meetingConfig.role,
				success: function (res) {
					meetingConfig.signature = res.result;
					meetingConfig.sdkKey    = meetingConfig.apiKey;
				},
			}
		);
		ZoomMtg.init(
			{
				leaveUrl: meetingConfig.leaveUrl,
				disableCORP: ! window.crossOriginIsolated,
				webEndpoint: meetingConfig.webEndpoint,
				success: function () {
					ZoomMtg.i18n.load( meetingConfig.lang );
					ZoomMtg.i18n.reload( meetingConfig.lang );
					let data = {
						meetingNumber: meetingConfig.meetingNumber,
						userName: meetingConfig.userName,
						signature: signature,
						sdkKey: meetingConfig.apiKey,
						userEmail: meetingConfig.userEmail,
						passWord: meetingConfig.passWord,
						success: function (res) {
							ZoomMtg.getAttendeeslist( {} );
							ZoomMtg.getCurrentUser(
								{
									success: function (res) {},
								}
							);
						},
						error: function (res) {},
					};
					if (enforce_login && tk) {
						data.tk = tk;
					}
					ZoomMtg.join( data );
				},
				error: function (res) {},
			}
		);
	}
	beginJoin();
};
