(function() {
	var fs = require("fs");
	var hall = require("hall-client");

	var hall_config = {
		email: process.env.HALL_EMAIL,
		password: process.env.HALL_PASSWORD,
		ua: {
			meta: "Hall-CWM-Adapter/1.0.0"
		}
	};

	var app = process.argv[1];
	if (process.argv.length < 4) {
		console.log("Usage: node %s room_id file_path".replace(/%s/, app));
		process.exit(1);
	}

	// required values
	var room_id = process.argv[2];
	var file = process.argv[3];

	// pass in user + password
	if (process.argv.length == 6) {
		hall_config.email = process.argv[4];
		hall_config.password = process.argv[5];
	}

	// open file
	fs.readFile(file, {encoding: "utf8"}, function (err, data) {
		if (err) {
			console.log("Usage: node %s room_id file_path".replace(/%s/, app));
			process.exit(1);
		}

		var standup = (""+data).replace(/^#-.*$\n/mg, "").replace(/^(\s|\u00A0)+|(\s|\u00A0)+$/g, "");
		if (standup) {
			var bot = new hall(hall_config);

			bot.on("message_sent", function() { process.exit(0); });
			bot.on("error", function() { process.exit(1); });
			bot.io.on("message_sent", function() { process.exit(0); });
			bot.io.on("error", function() { process.exit(1); });

			bot.io.on("connected", function() {
				bot.sendMessage(room_id, "group", standup);
			});
		}
	});

}).call(this);
