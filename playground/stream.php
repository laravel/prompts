<?php

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\stream;

require __DIR__ . '/../vendor/autoload.php';

confirm('Ready to rock?');

$toStream = <<<TEXT
Fog sat low over the coastal town like a wet blanket that tasted of salt and diesel, and Juniper moved through it nose-first, scruffy coat beading with mist, paws memorizing every plank and pothole by feel. Home—whatever that word meant—had always been a collage of smells: yesterday’s fish guts behind the cannery, old rope at the pier, the sharp soap of the shelter volunteer’s hands; tonight, though, a new scent snagged her like a burr—paper, pencil graphite, and the faint sweetness of a child’s snack—fluttering in the gutter beside the post office. She nosed it free: a drawing, creased and damp, of a lighthouse with a crooked yellow window, a small stick-figure dog beside it, and the word JUNI—half-written, trailing off as if the pencil had been yanked away. Juniper’s chest tightened with a strange, urgent warmth. Above the fog came another smell, metallic and cold, like rain sharpening its teeth. The storm was coming, fast, and she knew from the way humans moved when the air turned like this: the harbor bridge would close at dusk, sealing off the far spit of houses and the lighthouse road. If the child lived across it, the drawing would be stranded—like she had been, once, on the wrong side of a door.
TEXT;
// The mail carrier—Miles, the town called him, though Juniper knew him as leather-bag, paper-dust, and loneliness—stepped out into the fog with his cart squeaking. He smelled of coffee gone cold and the careful distance people wore when they didn’t want to need anything. Juniper trotted up and placed the drawing gently against his boot, then looked up, ears pricked, willing him to understand. Miles frowned. “Not yours,” he muttered, bending to pick it up between two fingers as if it might bite. Juniper’s tail thumped once—yes, not mine—and she nudged his hand, then turned and trotted three steps, glancing back. Come. The paper trembled in his grip. “You’re… delivering?” he said, skeptical, but the word tasted like curiosity instead of dismissal. Juniper sneezed in the fog—salt, rope, child—and set off.

// The first set piece was a puzzle made of invisible lines. On Main Street the wind braided smells into false trails: fried dough from the boardwalk, wet cedar from the boatyard, a smear of perfume that tried to masquerade as child. Juniper slowed, nose low, sorting layers the way she once sorted shelters—fear, hope, disinfectant. The drawing’s scent was the anchor: graphite and little fingers. She found it again at knee height where the fog was warmer, following it past the bakery’s yeasty breath, across the square’s rusted fountain, then—there, a new note: crayon wax and tears, fresh enough to sting. She stopped at the curb and looked back hard at Miles. He stood with his cart and his doubts, raincoat collar up, the drawing tucked against his chest like something fragile. “Alright,” he sighed, and stepped off the curb after her. His boots made a sound like decision.

// They hit the docks as the sky dimmed from pearl to bruised slate. Juniper’s second set piece was a chase along wet boards, because the scent trail didn’t stroll politely; it ran. A gust snatched the drawing from Miles’s hand—paper flashed white, then skittered like a frightened gull toward the edge of the pier. Juniper launched, claws scrabbling, fog swallowing her body until only her barking breath remained. She caught the paper between her teeth just as it tried to leap into black water, the taste of soggy pulp and graphite filling her mouth. The dock planks shuddered beneath her as a forklift beeped blind in the fog, and a rope swung loose like a snake. Juniper dodged, heart hammering, and barreled back into Miles’s legs, forcing the drawing into his hands with a shove of her muzzle. Miles’s fingers closed around it with unexpected gentleness. “Okay,” he whispered, more to himself than to her, “okay. I get it.”

$stream = stream();
$words = explode(' ', $toStream);

foreach ($words as $word) {
    $stream->append($word . ' ');
    usleep(25_000);
}

$stream->close();

echo str_repeat(PHP_EOL, 5);

// var_dump($stream->value());

// echo str_repeat(PHP_EOL, 5);
