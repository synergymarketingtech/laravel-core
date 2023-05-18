<?php

namespace Coderstm\Traits;

use Coderstm\Traits\Cashier\ManagesCustomer;
use Laravel\Cashier\Concerns\HandlesTaxes;
use Coderstm\Traits\Cashier\ManagesSubscriptions;
use Coderstm\Traits\Cashier\ManagesPaymentMethods;
use Laravel\Cashier\Concerns\ManagesInvoices;
use Laravel\Cashier\Concerns\PerformsCharges;

trait Billable
{
    use HandlesTaxes;
    use ManagesCustomer;
    use ManagesInvoices;
    use ManagesPaymentMethods;
    use ManagesSubscriptions;
    use PerformsCharges;
}
