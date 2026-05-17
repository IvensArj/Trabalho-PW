<?php

function ui_page($maxWidth = "max-w-5xl")
{
    return "mx-auto w-full {$maxWidth} px-4 py-10 sm:px-6 lg:px-8";
}

function ui_card($extra = "")
{
    return trim("rounded-lg border border-slate-200 bg-white shadow-sm {$extra}");
}

function ui_card_body($extra = "")
{
    return trim("p-5 {$extra}");
}

function ui_input($extra = "")
{
    return trim("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200 {$extra}");
}

function ui_button($variant = "primary", $size = "md", $extra = "")
{
    $base = "inline-flex items-center justify-center rounded-md font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2";

    $sizes = [
        "sm" => "px-3 py-1.5 text-xs",
        "md" => "px-4 py-2 text-sm",
    ];

    $variants = [
        "primary" => "bg-sky-600 text-white hover:bg-sky-700 focus:ring-sky-500",
        "secondary" => "bg-slate-600 text-white hover:bg-slate-700 focus:ring-slate-500",
        "success" => "bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500",
        "warning" => "bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500",
        "danger" => "bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500",
        "outline-danger" => "border border-rose-300 bg-white text-rose-700 hover:bg-rose-50 focus:ring-rose-500",
        "outline-secondary" => "border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-500",
    ];

    return trim($base . " " . ($sizes[$size] ?? $sizes["md"]) . " " . ($variants[$variant] ?? $variants["primary"]) . " " . $extra);
}

function ui_column_header($variant)
{
    $variants = [
        "todo" => "bg-slate-700 text-white",
        "doing" => "bg-sky-700 text-white",
        "done" => "bg-emerald-700 text-white",
    ];

    return "rounded-t-lg px-5 py-4 " . ($variants[$variant] ?? $variants["todo"]);
}
