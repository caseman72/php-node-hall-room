(function() {
	var fs = require("fs");
	var Hipchatter = require("hipchatter");
	var hipchatter = new Hipchatter("<key>");

	var app = process.argv[1];
	if (process.argv.length < 3) {
		console.log("Usage: node %s file_path".replace(/%s/, app));
		process.exit(1);
	}
	var file = process.argv[2];

	// open file
	fs.readFile(file, {encoding: "utf8"}, function (err, data) {
		if (err) {
			console.log("Usage: node %s file_path".replace(/%s/, app));
			process.exit(1);
		}

		var standup = (""+data).replace(/^#-.*$\n/mg, "").replace(/^(\s|\u00A0)+|(\s|\u00A0)+$/g, "");
		if (standup) {
			hipchatter.notify(
				"Project TARDIS",
				{
					message: "<pre>" + standup + "</pre>",
					color: "green",
					token: "<token>"
				},
				function(err) {
					if (err) {
						console.log(err);
						process.exit(1);
					}
					process.exit(0);
				}
			);
		}
	});

}).call(this);
