String _escapeField(String? value) {
  if (value == null) return '';
  final s = value.toString();
  if (s.contains(',') || s.contains('"') || s.contains('\n') || s.contains('\r')) {
    return '"${s.replaceAll('"', '""')}"';
  }
  return s;
}

String buildCsv(List<List<String?>> rows) {
  return rows.map((r) => r.map(_escapeField).join(',')).join('\r\n');
}
