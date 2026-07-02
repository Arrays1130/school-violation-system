/// Pure helpers for notification list pagination (testable).
class NotificationPagination {
  static Map<String, dynamic> applyPage({
    required List<dynamic> existing,
    required dynamic result,
    required bool reset,
    int currentPage = 1,
    int lastPage = 1,
  }) {
    List<dynamic> items = [];
    var page = currentPage;
    var last = lastPage;

    if (result is Map && result.containsKey('data')) {
      items = result['data'] as List<dynamic>;
      final meta = result['meta'];
      if (meta is Map) {
        page = meta['current_page'] ?? page;
        last = meta['last_page'] ?? last;
      }
    } else if (result is List) {
      items = result;
      page = 1;
      last = 1;
    }

    final merged = reset ? items : [...existing, ...items];

    return {
      'items': merged,
      'currentPage': page,
      'lastPage': last,
    };
  }
}
