<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum WebhookEndpointEvent: string
{
    case CheckoutSessionCompleted = 'checkout.session.completed';
    case CheckoutSessionClosed = 'checkout.session.closed';
    case SubscriptionActivated = 'subscription.activated';
    case SubscriptionRenewed = 'subscription.renewed';
    case SubscriptionChanged = 'subscription.changed';
    case SubscriptionCanceled = 'subscription.canceled';
    case SubscriptionOnGracePeriod = 'subscription.on_grace_period';
    case SubscriptionResumed = 'subscription.resumed';
    case SubscriptionEnded = 'subscription.ended';
    case RefundSucceeded = 'refund.succeeded';
}
