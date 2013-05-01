<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>{$pageTitle}</title>
<meta http-equiv="Content-Language" content="fr" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{foreach $additionalCss as $cssFile}
<link href="{$cssFile}" rel="stylesheet" type="text/css" />
{/foreach}
<script src="http://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript" charset="utf-8"></script>
{foreach $additionalJs as $jsFile}
<script src="{$jsFile}" type="text/javascript" charset="utf-8"></script>
{/foreach}
</head>
<body>