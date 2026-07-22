<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:generate-invoices')->monthlyOn(1, '02:00');
Schedule::command('billing:check-overdue')->daily();
Schedule::command('billing:check-overdue-emails')->daily();
Schedule::command('billing:unblock-paid')->daily();
