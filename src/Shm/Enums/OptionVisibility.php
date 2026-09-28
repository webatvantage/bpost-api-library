<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * Whether an option within a product is offered to the consumer.
 */
enum OptionVisibility: string
{
	case NotVisibleByConsumerOptional = 'NOT_VISIBLE_BY_CONSUMER_OPTIONAL';
	case NotVisibleByConsumerDefault = 'NOT_VISIBLE_BY_CONSUMER_DEFAULT';
	case VisibleByConsumerAndMandatory = 'VISIBLE_BY_CONSUMER_AND_MANDATORY';
}
