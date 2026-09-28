<?php

namespace Webatvantage\Bpost\Api\Enums;

/**
 * Media types shared by more than one bpost service.
 *
 * The versioned `application/vnd.bpost.*` types are per-service and per-operation — bpost does not
 * bump them in lockstep, so a v5 operation can still answer a v3.4 media type — and they live in
 * each domain's own enum rather than here.
 */
enum MediaType: string
{
	case ApplicationXml = 'application/xml';
	case ApplicationPdf = 'application/pdf';
	case ImagePng = 'image/png';
	case TextZpl = 'text/zpl';
}
