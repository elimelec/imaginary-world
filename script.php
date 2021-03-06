<?php 
	include 'missions_db.php';
	include 'kings_db.php';
	header('Content-Type: application/javascript');
?>

var ImaginaryWorld = {
	player: {
		name: "Player",
		hp: 100,
		xp: 0,
		time: "day",
		disableNameUpdate: false,
	},
	
	savingLoop: function() {
		setInterval(function() {
			ImaginaryWorld.save();
		}, 10000);
	},
	
	gameLoop: function() {
		setInterval(function() {
			ImaginaryWorld.loop();
			ImaginaryWorld.disableEnableFightKingButtons();
			ImaginaryWorld.updateVariables();
		}, 1000);
	},
	
	loop: function() {
		if(this.player.hp < 100) {
			this.player.hp++;
		}
	},
	
	disableEnableFightKingButtons: function() {
		$buttons = $(".fight_king");
		if(this.player.hp <= 20) {
			$buttons.attr("disabled", "disabled");
		} else {
			$buttons.removeAttr("disabled");
		}
	},
	
	start: function() {
		if(this.getItem("saved")) {
			this.load();
		}
		this.savingLoop();
		this.gameLoop();
		this.updateVariables();
		this.showHideMissions();
		this.changeTheme();
	},
	
	changeTheme: function() {
		switch(this.player.time) {
			case "day":
				$("body").addClass("day");
				$("body").removeClass("night");
				break;
			case "night":
				$("body").addClass("night");
				$("body").removeClass("day");
				break;
		}
		this.updateVariables();
	},
	
	showHideMissions: function() {
		switch(this.player.time) {
			case "day":
				$("#night").addClass("hidden");
				$("#day").removeClass("hidden");
				break;
			case "night":
				$("#day").addClass("hidden");
				$("#night").removeClass("hidden");
				break;
		}
	},
	
	changeMode: function() {
		if(this.player.time == "day") {
			this.player.time = "night";
		} else {
			this.player.time = "day";
		}
		this.showHideMissions();
		this.changeTheme();
	},
	
	updateVariables: function() {
		if(!this.player.disableNameUpdate) {
			$("#name").text(this.player.name);		
		}
		$("#hp").text(this.player.hp);
		$("#xp").text(this.player.xp);
		$("#time").text(this.player.time);
		
		for(var i = 0; i < this.day.missions.length; i++) {
			var id = "m"+i+"day";
			$("#"+id).text(this.getMissionsKingsProgress(id));
		}
		
		for(var i = 0; i < this.night.missions.length; i++) {
			var id = "m"+i+"night";
			$("#"+id).text(this.getMissionsKingsProgress(id));
		}
		
		for(var i = 0; i < this.day.kings.length; i++) {
			var id = "k"+i+"day";
			$("#"+id).text(this.getMissionsKingsProgress(id));
		}
		
		for(var i = 0; i < this.night.kings.length; i++) {
			var id = "k"+i+"night";
			$("#"+id).text(this.getMissionsKingsProgress(id));
		}
	},
	
	getMissionsKingsProgress: function(saveName) {
		saveValue = this.getItem(saveName);
		if(saveValue === null) {
			return 0;
		} 
		return parseInt(saveValue);
	},
	
	missionDone: function(id, time) {
		switch (time) {
		case "day":
			this.player.xp += this.day.missions[id].difficulty;
			break;
		case "night":
			this.player.xp += this.night.missions[id].difficulty;
			break;
		}
		this.saveMissionsKingsProgress("m", id, time);
		this.updateVariables();
	},
	
	saveMissionsKingsProgress: function(type, id, time) {
		var saveName = type+id+time;
		var previousValue = this.getItem(saveName);
		var saveValue;
		if(previousValue === null) {
			saveValue = 1;
		} else {
			saveValue = parseInt(previousValue) + 1;
		}
		this.setItem(saveName, saveValue);
	},
	
	fightKing: function(id, time) {
		var kingPower;
		var yourPower = this.player.xp;
		switch (time) {
		case "day":
			kingPower = this.day.kings[id].difficulty;
			break;
		case "night":
			kingPower = this.night.kings[id].difficulty;
			break;
		}
		if (yourPower <= kingPower) {
			this.player.xp = 0;
			this.player.hp = 1;
		} else {
			this.player.xp -= kingPower;
			this.player.hp -= 20;
			this.saveMissionsKingsProgress("k", id, time);
		}
		this.disableEnableFightKingButtons();
		this.updateVariables();
	},
	
	save: function() {
		this.setItem("hp", this.player.hp);
		this.setItem("name", this.player.name);
		this.setItem("xp", this.player.xp);
		this.setItem("time", this.player.time);
		this.setItem("saved", true);
	},
	
	load: function() {
		this.player.hp = parseInt(this.getItem("hp"));
		this.player.name = this.getItem("name");
		this.player.xp = parseInt(this.getItem("xp"));
		this.player.time = this.getItem("time");
	},
	
	setItem: function(key, value) {
		localStorage.setItem(key, value);
	},
	
	getItem: function(key) {
		return localStorage.getItem(key);
	},
	
	updateName: function() {
		this.player.name = $("#name").text();
		this.player.disableNameUpdate = false;
		this.updateVariables();	
	},
	
	day: {
		missions: [
			<?php
			foreach ($day_missions as $mission) {
				print "{id:{$mission->id},title:'{$mission->title}',description:'{$mission->description}',difficulty:{$mission->difficulty}},";		
			}
			?>
		],
		
		kings: [
			<?php
			foreach ($day_kings as $king) {
				print "{id:{$king->id},title:'{$king->title}',description:'{$king->description}',difficulty:{$king->difficulty}},";		
			}
			?>
		]
	},
	
	night: {
		missions: [
			<?php
			foreach ($night_missions as $mission) {
				print "{id:{$mission->id},title:'{$mission->title}',description:'{$mission->description}',difficulty:{$mission->difficulty}},";		
			}
			?>
		],
		
		kings: [
			<?php
			foreach ($night_kings as $king) {
				print "{id:{$king->id},title:'{$king->title}',description:'{$king->description}',difficulty:{$king->difficulty}},";		
			}
			?>
		]
	}};
