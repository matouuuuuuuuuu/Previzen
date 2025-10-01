<?php

function estimateWaterTemp($airTemp) {
    // Estimation simple : eau légèrement plus froide que l’air
    return max(10, round($airTemp - rand(2, 6), 1));
}



?>