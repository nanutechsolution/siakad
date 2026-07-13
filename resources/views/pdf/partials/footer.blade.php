<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("Helvetica", "normal");
        $size = 8;
        $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
        $width = $fontMetrics->getTextWidth($text, $font, $size);
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size, [0.3, 0.3, 0.3]);
    }
</script>