import 'dart:io';
import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

/// Opens a background native SQLite connection using Drift's background isolate.
LazyDatabase openDatabaseConnection({bool inMemory = false}) {
  if (inMemory) {
    return LazyDatabase(() async => NativeDatabase.memory());
  }

  return LazyDatabase(() async {
    final dbFolder = await getApplicationDocumentsDirectory();
    final file = File(p.join(dbFolder.path, 'exponit_field.sqlite'));
    return NativeDatabase.createInBackground(file);
  });
}
