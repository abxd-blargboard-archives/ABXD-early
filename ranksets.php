<?php
//  AcmlmBoard XD - Source file for addranks.php
//  Access: N/A

//Rank images should match the rank text, but without punctuation or spaces.
//Possible settings for a rankset include:
// "name" -- required, name displayed in choices
// "directory" -- name of rankset image directory, defaults to name
// "notolower" -- bool, disables str_tolower() calls, e.g. canonical Zelda set
// "noimages" -- bool, disables automajickal conversion to <img> tags
// "splitlines" -- bool, enables <br /> insertion
//Note that any rank below 10 posts is assumed text only as if noimages was set.

$ranks = array
(
	array
	(
		"name" => "Mario",
		"ranks" => array
		(
			0 => "Non-poster",
			1 => "Newcomer",
			10 => "Micro-Goomba",
			20 => "Goomba",
			35 => "Red Goomba",
			50 => "Red Paragoomba",
			65 => "Paragoomba",
			80 => "Shyguy",
			100 => "Koopa",
			120 => "Red Koopa",
			140 => "Paratroopa",
			160 => "Red Paratroopa",
			180 => "Cheep-cheep",
			200 => "Red Cheep-cheep",
			225 => "Ninji",
			250 => "Flurry",
			275 => "Snifit",
			300 => "Porcupo",
			325 => "Panser",
			350 => "Mole",
			375 => "Buzzy Beetle",
			400 => "Nipper Plant",
			425 => "Bloober",
			450 => "Buster Beetle",
			475 => "Beezo",
			500 => "Bullet Bill",
			525 => "Rex",
			550 => "Lakitu",
			575 => "Spiny",
			600 => "Bob-Omb",
			625 => "Dry Bones",
			650 => "Cobrat",
			675 => "Pokey",
			700 => "Spike",
			725 => "Melon Bug",
			750 => "Lantern Ghost",
			775 => "Fuzzy",
			800 => "Bandit",
			830 => "Super Koopa",
			860 => "Red Super Koopa",
			900 => "Boo",
			925 => "Boo",
			950 => "Fuzz Ball",
			1000 => "Boomerang Brother",
			1050 => "Hammer Brother",
			1100 => "Fire Brother",
			1150 => "Fire Snake",
			1200 => "Giant Goomba",
			1250 => "Giant Koopa",
			1300 => "Giant Red Koopa",
			1350 => "Giant Paratroopa",
			1400 => "Giant Red Paratroopa",
			1450 => "Chuck",
			1500 => "Thwomp",
			1550 => "Boss Bass",
			1600 => "Volcano Lotus",
			1650 => "Lava Lotus",
			1700 => "Ptooie",
			1800 => "Sledge Brother",
			1900 => "Boomboom",
			2000 => "Birdo",
			2100 => "Red Birdo",
			2200 => "Green Birdo",
			2300 => "Larry Koopa",
			2400 => "Morton Koopa",
			2500 => "Wendy Koopa",
			2600 => "Iggy Koopa",
			2700 => "Roy Koopa",
			2800 => "Lemmy Koopa",
			2900 => "Ludwig Von Koopa",
			3000 => "Triclyde",
			3100 => "Magikoopa",
			3200 => "Wart",
			3300 => "Baby Bowser",
			3400 => "King Bowser Koopa",
			3500 => "Yoshi",
			3600 => "Yellow Yoshi",
			3700 => "Blue Yoshi",
			3800 => "Red Yoshi",
			3900 => "King Yoshi",
			4000 => "Baby Mario",
			4100 => "Luigi",
			4200 => "Mario",
			4300 => "Super Luigi",
			4400 => "Super Mario",
			4500 => "Fire Luigi",
			4600 => "Fire Mario",
			4700 => "Cape Luigi",
			4800 => "Cape Mario",
			4900 => "Star Luigi",
			5000 => "Star Mario",
		)
	),

	array
	(
		"name" => "Touhou",
		"noimages" => 1,
		"ranks" => array
		(
			10 => "<img src=\"img/ranks/touhou/reimu.gif\" alt=\"reimu\"><br />Reimu Hakurei",
			20 => "<img src=\"img/ranks/touhou/marisa.gif\" alt=\"marisa\"><br />Marisa Kirisame",

			30 => "<img src=\"img/ranks/touhou/rumia.gif\" alt=\"rumia\"><br />Rumia",
			40 => "<img src=\"img/ranks/touhou/cirno.gif\" alt=\"cirno\"><br />Cirno",
			50 => "<img src=\"img/ranks/touhou/china.gif\" alt=\"china\"><br />Hong Meilin",
			60 => "<img src=\"img/ranks/touhou/patchouli.gif\" alt=\"patchouli\"><br />Patchouli Knowledge",
			70 => "<img src=\"img/ranks/touhou/sakuya.gif\" alt=\"sakuya\"><br />Sakuya Izayoi",
			80 => "<img src=\"img/ranks/touhou/remilia.gif\" alt=\"remilia\"><br />Remilia Scarlet",
			90 => "<img src=\"img/ranks/touhou/flandre.gif\" alt=\"flandre\"><br />Flandre Scarlet",

			100 => "<img src=\"img/ranks/touhou/letty.gif\" alt=\"letty\"><br />Letty Whiterock",
			120 => "<img src=\"img/ranks/touhou/chen.gif\" alt=\"chen\"><br />Chen",
			140 => "<img src=\"img/ranks/touhou/alice.gif\" alt=\"alice\"><br />Alice Margatroid",
			160 => "<img src=\"img/ranks/touhou/lily.gif\" alt=\"lily\"><br />Lily White",
			180 => "<img src=\"img/ranks/touhou/lunasa.gif\" alt=\"lunasa\"><br />Lunasa Prismriver",
			200 => "<img src=\"img/ranks/touhou/merlin.gif\" alt=\"merlin\"><br />Merlin Prismriver",
			220 => "<img src=\"img/ranks/touhou/lyrica.gif\" alt=\"lyrica\"><br />Lyrica Prismriver",
			240 => "<img src=\"img/ranks/touhou/youmu.gif\" alt=\"youmu\"><br />Youmu Konpaku",
			260 => "<img src=\"img/ranks/touhou/yuyuko.gif\" alt=\"yuyuko\"><br />Yuyuko Saigyouzi",
			280 => "<img src=\"img/ranks/touhou/ran.gif\" alt=\"ran\"><br />Ran Yakumo",
			300 => "<img src=\"img/ranks/touhou/yukari.gif\" alt=\"yukari\"><br />Yukari Yakumo",

			325 => "<img src=\"img/ranks/touhou/suika.gif\" alt=\"suika\"><br />Suika Ibuki",

			350 => "<img src=\"img/ranks/touhou/wriggle.gif\" alt=\"wriggle\"><br />Wriggle Nightbug",
			400 => "<img src=\"img/ranks/touhou/mystia.gif\" alt=\"mystia\"><br />Mystia Lorelei",
			450 => "<img src=\"img/ranks/touhou/keine.gif\" alt=\"keine\"><br />Keine Kamishirasawa",
			500 => "<img src=\"img/ranks/touhou/tewi.gif\" alt=\"tewi\"><br />Tewi Inaba",
			550 => "<img src=\"img/ranks/touhou/reisen.gif\" alt=\"reisen\"><br />Reisen Udongein Inaba",
			600 => "<img src=\"img/ranks/touhou/eirin.gif\" alt=\"eirin\"><br />Eirin Yagokoro",
			650 => "<img src=\"img/ranks/touhou/kaguya.gif\" alt=\"kaguya\"><br />Kaguya Houraisan",
			700 => "<img src=\"img/ranks/touhou/mokou.gif\" alt=\"mokou\"><br />Fujiwara no Mokou",

			800 => "<img src=\"img/ranks/touhou/aya.gif\" alt=\"aya\"><br />Aya Shiameimaru",
			900 => "<img src=\"img/ranks/touhou/medicine.gif\" alt=\"medicine\"><br />Medicine Melancholy",
			1000 => "<img src=\"img/ranks/touhou/yuka.gif\" alt=\"yuka\"><br />Yuka Kazami",
			1100 => "<img src=\"img/ranks/touhou/komachi.gif\" alt=\"komachi\"><br />Komachi Onozuka",
			1200 => "<img src=\"img/ranks/touhou/shiki.gif\" alt=\"shiki\"><br />Shikieiki Yamaxanadu",

			1400 => "<img src=\"img/ranks/touhou/shizuha.gif\" alt=\"shizuha\"><br />Shizuha Aki",
			1600 => "<img src=\"img/ranks/touhou/minoriko.gif\" alt=\"minoriko\"><br />Minoriko Aki",
			1800 => "<img src=\"img/ranks/touhou/hina.gif\" alt=\"hina\"><br />Hina Kagiyama",
			2000 => "<img src=\"img/ranks/touhou/nitori.gif\" alt=\"nitori\"><br />Nitori MacGyver",
			2500 => "<img src=\"img/ranks/touhou/momizi.gif\" alt=\"momizi\"><br />Momizi Inubashiri",
			3000 => "<img src=\"img/ranks/touhou/sanae.gif\" alt=\"sanae\"><br />Sanae Kotiya",
			4000 => "<img src=\"img/ranks/touhou/kanako.gif\" alt=\"kanako\"><br />Kanako Yasaka",
			5000 => "<img src=\"img/ranks/touhou/suwako.gif\" alt=\"suwako\"><br />Suwako Moriya",
		)
	),
);

?>
