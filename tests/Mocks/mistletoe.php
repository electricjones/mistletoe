<?php

return (new \ElectricJones\Mistletoe\TaskPlanner())
    ->add('Me\And\You')->always()
    ->add('Another\One')->daily()->at('1:00')->followedBy('Something\Else');
