<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Detailing Visit Cost
    |--------------------------------------------------------------------------
    |
    | Baseline expenditure allocated per medical representative clinic detailing
    | call. Can be overridden on a per-doctor basis via doctors.visit_cost_override.
    |
    */
    'default_visit_cost' => (float) env('EXPONIT_DEFAULT_VISIT_COST', 250.00),
];
