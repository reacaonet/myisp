<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\GenerateInvoices;
use App\Console\Commands\MarkOverdueInvoices;
use App\Console\Commands\AutoBlock;

Schedule::command('invoices:mark-overdue')->daily();
Schedule::command('invoices:generate')->monthlyOn(1, '02:00');
Schedule::command('clients:auto-block --days=5')->daily();
Schedule::command('notifications:overdue --days=3')->dailyAt('09:00');
