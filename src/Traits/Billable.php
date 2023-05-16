<?php

namespace Coderstm\Core\Traits;

use Coderstm\Core\Traits\Cashier\ManagesCustomer;
use Laravel\Cashier\Concerns\HandlesTaxes;
use Coderstm\Core\Traits\Cashier\ManagesSubscriptions;
use Coderstm\Core\Traits\Cashier\ManagesPaymentMethods;
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
