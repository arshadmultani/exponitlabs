// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_database.dart';

// ignore_for_file: type=lint
class $DoctorsTable extends Doctors
    with TableInfo<$DoctorsTable, DoctorEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $DoctorsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _uuidMeta = const VerificationMeta('uuid');
  @override
  late final GeneratedColumn<String> uuid = GeneratedColumn<String>(
    'uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _nameMeta = const VerificationMeta('name');
  @override
  late final GeneratedColumn<String> name = GeneratedColumn<String>(
    'name',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _emailMeta = const VerificationMeta('email');
  @override
  late final GeneratedColumn<String> email = GeneratedColumn<String>(
    'email',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _phoneMeta = const VerificationMeta('phone');
  @override
  late final GeneratedColumn<String> phone = GeneratedColumn<String>(
    'phone',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _specialtyMeta = const VerificationMeta(
    'specialty',
  );
  @override
  late final GeneratedColumn<String> specialty = GeneratedColumn<String>(
    'specialty',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _qualificationMeta = const VerificationMeta(
    'qualification',
  );
  @override
  late final GeneratedColumn<String> qualification = GeneratedColumn<String>(
    'qualification',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _townMeta = const VerificationMeta('town');
  @override
  late final GeneratedColumn<String> town = GeneratedColumn<String>(
    'town',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _areaIdMeta = const VerificationMeta('areaId');
  @override
  late final GeneratedColumn<int> areaId = GeneratedColumn<int>(
    'area_id',
    aliasedName,
    true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _clinicNameMeta = const VerificationMeta(
    'clinicName',
  );
  @override
  late final GeneratedColumn<String> clinicName = GeneratedColumn<String>(
    'clinic_name',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _addressMeta = const VerificationMeta(
    'address',
  );
  @override
  late final GeneratedColumn<String> address = GeneratedColumn<String>(
    'address',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _latitudeMeta = const VerificationMeta(
    'latitude',
  );
  @override
  late final GeneratedColumn<double> latitude = GeneratedColumn<double>(
    'latitude',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _longitudeMeta = const VerificationMeta(
    'longitude',
  );
  @override
  late final GeneratedColumn<double> longitude = GeneratedColumn<double>(
    'longitude',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _statusMeta = const VerificationMeta('status');
  @override
  late final GeneratedColumn<String> status = GeneratedColumn<String>(
    'status',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('active'),
  );
  static const VerificationMeta _syncStatusMeta = const VerificationMeta(
    'syncStatus',
  );
  @override
  late final GeneratedColumn<String> syncStatus = GeneratedColumn<String>(
    'sync_status',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('synced'),
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    true,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: false,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    uuid,
    name,
    email,
    phone,
    specialty,
    qualification,
    town,
    areaId,
    clinicName,
    address,
    latitude,
    longitude,
    status,
    syncStatus,
    updatedAt,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'doctors';
  @override
  VerificationContext validateIntegrity(
    Insertable<DoctorEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('uuid')) {
      context.handle(
        _uuidMeta,
        uuid.isAcceptableOrUnknown(data['uuid']!, _uuidMeta),
      );
    } else if (isInserting) {
      context.missing(_uuidMeta);
    }
    if (data.containsKey('name')) {
      context.handle(
        _nameMeta,
        name.isAcceptableOrUnknown(data['name']!, _nameMeta),
      );
    } else if (isInserting) {
      context.missing(_nameMeta);
    }
    if (data.containsKey('email')) {
      context.handle(
        _emailMeta,
        email.isAcceptableOrUnknown(data['email']!, _emailMeta),
      );
    }
    if (data.containsKey('phone')) {
      context.handle(
        _phoneMeta,
        phone.isAcceptableOrUnknown(data['phone']!, _phoneMeta),
      );
    }
    if (data.containsKey('specialty')) {
      context.handle(
        _specialtyMeta,
        specialty.isAcceptableOrUnknown(data['specialty']!, _specialtyMeta),
      );
    }
    if (data.containsKey('qualification')) {
      context.handle(
        _qualificationMeta,
        qualification.isAcceptableOrUnknown(
          data['qualification']!,
          _qualificationMeta,
        ),
      );
    }
    if (data.containsKey('town')) {
      context.handle(
        _townMeta,
        town.isAcceptableOrUnknown(data['town']!, _townMeta),
      );
    }
    if (data.containsKey('area_id')) {
      context.handle(
        _areaIdMeta,
        areaId.isAcceptableOrUnknown(data['area_id']!, _areaIdMeta),
      );
    }
    if (data.containsKey('clinic_name')) {
      context.handle(
        _clinicNameMeta,
        clinicName.isAcceptableOrUnknown(data['clinic_name']!, _clinicNameMeta),
      );
    }
    if (data.containsKey('address')) {
      context.handle(
        _addressMeta,
        address.isAcceptableOrUnknown(data['address']!, _addressMeta),
      );
    }
    if (data.containsKey('latitude')) {
      context.handle(
        _latitudeMeta,
        latitude.isAcceptableOrUnknown(data['latitude']!, _latitudeMeta),
      );
    }
    if (data.containsKey('longitude')) {
      context.handle(
        _longitudeMeta,
        longitude.isAcceptableOrUnknown(data['longitude']!, _longitudeMeta),
      );
    }
    if (data.containsKey('status')) {
      context.handle(
        _statusMeta,
        status.isAcceptableOrUnknown(data['status']!, _statusMeta),
      );
    }
    if (data.containsKey('sync_status')) {
      context.handle(
        _syncStatusMeta,
        syncStatus.isAcceptableOrUnknown(data['sync_status']!, _syncStatusMeta),
      );
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {uuid};
  @override
  DoctorEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return DoctorEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      ),
      uuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}uuid'],
      )!,
      name: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}name'],
      )!,
      email: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}email'],
      ),
      phone: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}phone'],
      ),
      specialty: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}specialty'],
      ),
      qualification: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}qualification'],
      ),
      town: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}town'],
      ),
      areaId: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}area_id'],
      ),
      clinicName: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}clinic_name'],
      ),
      address: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}address'],
      ),
      latitude: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}latitude'],
      ),
      longitude: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}longitude'],
      ),
      status: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}status'],
      )!,
      syncStatus: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}sync_status'],
      )!,
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      ),
    );
  }

  @override
  $DoctorsTable createAlias(String alias) {
    return $DoctorsTable(attachedDatabase, alias);
  }
}

class DoctorEntity extends DataClass implements Insertable<DoctorEntity> {
  final int? id;
  final String uuid;
  final String name;
  final String? email;
  final String? phone;
  final String? specialty;
  final String? qualification;
  final String? town;
  final int? areaId;
  final String? clinicName;
  final String? address;
  final double? latitude;
  final double? longitude;
  final String status;
  final String syncStatus;
  final DateTime? updatedAt;
  const DoctorEntity({
    this.id,
    required this.uuid,
    required this.name,
    this.email,
    this.phone,
    this.specialty,
    this.qualification,
    this.town,
    this.areaId,
    this.clinicName,
    this.address,
    this.latitude,
    this.longitude,
    required this.status,
    required this.syncStatus,
    this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (!nullToAbsent || id != null) {
      map['id'] = Variable<int>(id);
    }
    map['uuid'] = Variable<String>(uuid);
    map['name'] = Variable<String>(name);
    if (!nullToAbsent || email != null) {
      map['email'] = Variable<String>(email);
    }
    if (!nullToAbsent || phone != null) {
      map['phone'] = Variable<String>(phone);
    }
    if (!nullToAbsent || specialty != null) {
      map['specialty'] = Variable<String>(specialty);
    }
    if (!nullToAbsent || qualification != null) {
      map['qualification'] = Variable<String>(qualification);
    }
    if (!nullToAbsent || town != null) {
      map['town'] = Variable<String>(town);
    }
    if (!nullToAbsent || areaId != null) {
      map['area_id'] = Variable<int>(areaId);
    }
    if (!nullToAbsent || clinicName != null) {
      map['clinic_name'] = Variable<String>(clinicName);
    }
    if (!nullToAbsent || address != null) {
      map['address'] = Variable<String>(address);
    }
    if (!nullToAbsent || latitude != null) {
      map['latitude'] = Variable<double>(latitude);
    }
    if (!nullToAbsent || longitude != null) {
      map['longitude'] = Variable<double>(longitude);
    }
    map['status'] = Variable<String>(status);
    map['sync_status'] = Variable<String>(syncStatus);
    if (!nullToAbsent || updatedAt != null) {
      map['updated_at'] = Variable<DateTime>(updatedAt);
    }
    return map;
  }

  DoctorsCompanion toCompanion(bool nullToAbsent) {
    return DoctorsCompanion(
      id: id == null && nullToAbsent ? const Value.absent() : Value(id),
      uuid: Value(uuid),
      name: Value(name),
      email: email == null && nullToAbsent
          ? const Value.absent()
          : Value(email),
      phone: phone == null && nullToAbsent
          ? const Value.absent()
          : Value(phone),
      specialty: specialty == null && nullToAbsent
          ? const Value.absent()
          : Value(specialty),
      qualification: qualification == null && nullToAbsent
          ? const Value.absent()
          : Value(qualification),
      town: town == null && nullToAbsent ? const Value.absent() : Value(town),
      areaId: areaId == null && nullToAbsent
          ? const Value.absent()
          : Value(areaId),
      clinicName: clinicName == null && nullToAbsent
          ? const Value.absent()
          : Value(clinicName),
      address: address == null && nullToAbsent
          ? const Value.absent()
          : Value(address),
      latitude: latitude == null && nullToAbsent
          ? const Value.absent()
          : Value(latitude),
      longitude: longitude == null && nullToAbsent
          ? const Value.absent()
          : Value(longitude),
      status: Value(status),
      syncStatus: Value(syncStatus),
      updatedAt: updatedAt == null && nullToAbsent
          ? const Value.absent()
          : Value(updatedAt),
    );
  }

  factory DoctorEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return DoctorEntity(
      id: serializer.fromJson<int?>(json['id']),
      uuid: serializer.fromJson<String>(json['uuid']),
      name: serializer.fromJson<String>(json['name']),
      email: serializer.fromJson<String?>(json['email']),
      phone: serializer.fromJson<String?>(json['phone']),
      specialty: serializer.fromJson<String?>(json['specialty']),
      qualification: serializer.fromJson<String?>(json['qualification']),
      town: serializer.fromJson<String?>(json['town']),
      areaId: serializer.fromJson<int?>(json['areaId']),
      clinicName: serializer.fromJson<String?>(json['clinicName']),
      address: serializer.fromJson<String?>(json['address']),
      latitude: serializer.fromJson<double?>(json['latitude']),
      longitude: serializer.fromJson<double?>(json['longitude']),
      status: serializer.fromJson<String>(json['status']),
      syncStatus: serializer.fromJson<String>(json['syncStatus']),
      updatedAt: serializer.fromJson<DateTime?>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int?>(id),
      'uuid': serializer.toJson<String>(uuid),
      'name': serializer.toJson<String>(name),
      'email': serializer.toJson<String?>(email),
      'phone': serializer.toJson<String?>(phone),
      'specialty': serializer.toJson<String?>(specialty),
      'qualification': serializer.toJson<String?>(qualification),
      'town': serializer.toJson<String?>(town),
      'areaId': serializer.toJson<int?>(areaId),
      'clinicName': serializer.toJson<String?>(clinicName),
      'address': serializer.toJson<String?>(address),
      'latitude': serializer.toJson<double?>(latitude),
      'longitude': serializer.toJson<double?>(longitude),
      'status': serializer.toJson<String>(status),
      'syncStatus': serializer.toJson<String>(syncStatus),
      'updatedAt': serializer.toJson<DateTime?>(updatedAt),
    };
  }

  DoctorEntity copyWith({
    Value<int?> id = const Value.absent(),
    String? uuid,
    String? name,
    Value<String?> email = const Value.absent(),
    Value<String?> phone = const Value.absent(),
    Value<String?> specialty = const Value.absent(),
    Value<String?> qualification = const Value.absent(),
    Value<String?> town = const Value.absent(),
    Value<int?> areaId = const Value.absent(),
    Value<String?> clinicName = const Value.absent(),
    Value<String?> address = const Value.absent(),
    Value<double?> latitude = const Value.absent(),
    Value<double?> longitude = const Value.absent(),
    String? status,
    String? syncStatus,
    Value<DateTime?> updatedAt = const Value.absent(),
  }) => DoctorEntity(
    id: id.present ? id.value : this.id,
    uuid: uuid ?? this.uuid,
    name: name ?? this.name,
    email: email.present ? email.value : this.email,
    phone: phone.present ? phone.value : this.phone,
    specialty: specialty.present ? specialty.value : this.specialty,
    qualification: qualification.present
        ? qualification.value
        : this.qualification,
    town: town.present ? town.value : this.town,
    areaId: areaId.present ? areaId.value : this.areaId,
    clinicName: clinicName.present ? clinicName.value : this.clinicName,
    address: address.present ? address.value : this.address,
    latitude: latitude.present ? latitude.value : this.latitude,
    longitude: longitude.present ? longitude.value : this.longitude,
    status: status ?? this.status,
    syncStatus: syncStatus ?? this.syncStatus,
    updatedAt: updatedAt.present ? updatedAt.value : this.updatedAt,
  );
  DoctorEntity copyWithCompanion(DoctorsCompanion data) {
    return DoctorEntity(
      id: data.id.present ? data.id.value : this.id,
      uuid: data.uuid.present ? data.uuid.value : this.uuid,
      name: data.name.present ? data.name.value : this.name,
      email: data.email.present ? data.email.value : this.email,
      phone: data.phone.present ? data.phone.value : this.phone,
      specialty: data.specialty.present ? data.specialty.value : this.specialty,
      qualification: data.qualification.present
          ? data.qualification.value
          : this.qualification,
      town: data.town.present ? data.town.value : this.town,
      areaId: data.areaId.present ? data.areaId.value : this.areaId,
      clinicName: data.clinicName.present
          ? data.clinicName.value
          : this.clinicName,
      address: data.address.present ? data.address.value : this.address,
      latitude: data.latitude.present ? data.latitude.value : this.latitude,
      longitude: data.longitude.present ? data.longitude.value : this.longitude,
      status: data.status.present ? data.status.value : this.status,
      syncStatus: data.syncStatus.present
          ? data.syncStatus.value
          : this.syncStatus,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('DoctorEntity(')
          ..write('id: $id, ')
          ..write('uuid: $uuid, ')
          ..write('name: $name, ')
          ..write('email: $email, ')
          ..write('phone: $phone, ')
          ..write('specialty: $specialty, ')
          ..write('qualification: $qualification, ')
          ..write('town: $town, ')
          ..write('areaId: $areaId, ')
          ..write('clinicName: $clinicName, ')
          ..write('address: $address, ')
          ..write('latitude: $latitude, ')
          ..write('longitude: $longitude, ')
          ..write('status: $status, ')
          ..write('syncStatus: $syncStatus, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    uuid,
    name,
    email,
    phone,
    specialty,
    qualification,
    town,
    areaId,
    clinicName,
    address,
    latitude,
    longitude,
    status,
    syncStatus,
    updatedAt,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is DoctorEntity &&
          other.id == this.id &&
          other.uuid == this.uuid &&
          other.name == this.name &&
          other.email == this.email &&
          other.phone == this.phone &&
          other.specialty == this.specialty &&
          other.qualification == this.qualification &&
          other.town == this.town &&
          other.areaId == this.areaId &&
          other.clinicName == this.clinicName &&
          other.address == this.address &&
          other.latitude == this.latitude &&
          other.longitude == this.longitude &&
          other.status == this.status &&
          other.syncStatus == this.syncStatus &&
          other.updatedAt == this.updatedAt);
}

class DoctorsCompanion extends UpdateCompanion<DoctorEntity> {
  final Value<int?> id;
  final Value<String> uuid;
  final Value<String> name;
  final Value<String?> email;
  final Value<String?> phone;
  final Value<String?> specialty;
  final Value<String?> qualification;
  final Value<String?> town;
  final Value<int?> areaId;
  final Value<String?> clinicName;
  final Value<String?> address;
  final Value<double?> latitude;
  final Value<double?> longitude;
  final Value<String> status;
  final Value<String> syncStatus;
  final Value<DateTime?> updatedAt;
  final Value<int> rowid;
  const DoctorsCompanion({
    this.id = const Value.absent(),
    this.uuid = const Value.absent(),
    this.name = const Value.absent(),
    this.email = const Value.absent(),
    this.phone = const Value.absent(),
    this.specialty = const Value.absent(),
    this.qualification = const Value.absent(),
    this.town = const Value.absent(),
    this.areaId = const Value.absent(),
    this.clinicName = const Value.absent(),
    this.address = const Value.absent(),
    this.latitude = const Value.absent(),
    this.longitude = const Value.absent(),
    this.status = const Value.absent(),
    this.syncStatus = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  DoctorsCompanion.insert({
    this.id = const Value.absent(),
    required String uuid,
    required String name,
    this.email = const Value.absent(),
    this.phone = const Value.absent(),
    this.specialty = const Value.absent(),
    this.qualification = const Value.absent(),
    this.town = const Value.absent(),
    this.areaId = const Value.absent(),
    this.clinicName = const Value.absent(),
    this.address = const Value.absent(),
    this.latitude = const Value.absent(),
    this.longitude = const Value.absent(),
    this.status = const Value.absent(),
    this.syncStatus = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  }) : uuid = Value(uuid),
       name = Value(name);
  static Insertable<DoctorEntity> custom({
    Expression<int>? id,
    Expression<String>? uuid,
    Expression<String>? name,
    Expression<String>? email,
    Expression<String>? phone,
    Expression<String>? specialty,
    Expression<String>? qualification,
    Expression<String>? town,
    Expression<int>? areaId,
    Expression<String>? clinicName,
    Expression<String>? address,
    Expression<double>? latitude,
    Expression<double>? longitude,
    Expression<String>? status,
    Expression<String>? syncStatus,
    Expression<DateTime>? updatedAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (uuid != null) 'uuid': uuid,
      if (name != null) 'name': name,
      if (email != null) 'email': email,
      if (phone != null) 'phone': phone,
      if (specialty != null) 'specialty': specialty,
      if (qualification != null) 'qualification': qualification,
      if (town != null) 'town': town,
      if (areaId != null) 'area_id': areaId,
      if (clinicName != null) 'clinic_name': clinicName,
      if (address != null) 'address': address,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
      if (status != null) 'status': status,
      if (syncStatus != null) 'sync_status': syncStatus,
      if (updatedAt != null) 'updated_at': updatedAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  DoctorsCompanion copyWith({
    Value<int?>? id,
    Value<String>? uuid,
    Value<String>? name,
    Value<String?>? email,
    Value<String?>? phone,
    Value<String?>? specialty,
    Value<String?>? qualification,
    Value<String?>? town,
    Value<int?>? areaId,
    Value<String?>? clinicName,
    Value<String?>? address,
    Value<double?>? latitude,
    Value<double?>? longitude,
    Value<String>? status,
    Value<String>? syncStatus,
    Value<DateTime?>? updatedAt,
    Value<int>? rowid,
  }) {
    return DoctorsCompanion(
      id: id ?? this.id,
      uuid: uuid ?? this.uuid,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      specialty: specialty ?? this.specialty,
      qualification: qualification ?? this.qualification,
      town: town ?? this.town,
      areaId: areaId ?? this.areaId,
      clinicName: clinicName ?? this.clinicName,
      address: address ?? this.address,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      status: status ?? this.status,
      syncStatus: syncStatus ?? this.syncStatus,
      updatedAt: updatedAt ?? this.updatedAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (uuid.present) {
      map['uuid'] = Variable<String>(uuid.value);
    }
    if (name.present) {
      map['name'] = Variable<String>(name.value);
    }
    if (email.present) {
      map['email'] = Variable<String>(email.value);
    }
    if (phone.present) {
      map['phone'] = Variable<String>(phone.value);
    }
    if (specialty.present) {
      map['specialty'] = Variable<String>(specialty.value);
    }
    if (qualification.present) {
      map['qualification'] = Variable<String>(qualification.value);
    }
    if (town.present) {
      map['town'] = Variable<String>(town.value);
    }
    if (areaId.present) {
      map['area_id'] = Variable<int>(areaId.value);
    }
    if (clinicName.present) {
      map['clinic_name'] = Variable<String>(clinicName.value);
    }
    if (address.present) {
      map['address'] = Variable<String>(address.value);
    }
    if (latitude.present) {
      map['latitude'] = Variable<double>(latitude.value);
    }
    if (longitude.present) {
      map['longitude'] = Variable<double>(longitude.value);
    }
    if (status.present) {
      map['status'] = Variable<String>(status.value);
    }
    if (syncStatus.present) {
      map['sync_status'] = Variable<String>(syncStatus.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('DoctorsCompanion(')
          ..write('id: $id, ')
          ..write('uuid: $uuid, ')
          ..write('name: $name, ')
          ..write('email: $email, ')
          ..write('phone: $phone, ')
          ..write('specialty: $specialty, ')
          ..write('qualification: $qualification, ')
          ..write('town: $town, ')
          ..write('areaId: $areaId, ')
          ..write('clinicName: $clinicName, ')
          ..write('address: $address, ')
          ..write('latitude: $latitude, ')
          ..write('longitude: $longitude, ')
          ..write('status: $status, ')
          ..write('syncStatus: $syncStatus, ')
          ..write('updatedAt: $updatedAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $ProductsTable extends Products
    with TableInfo<$ProductsTable, ProductEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $ProductsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _nameMeta = const VerificationMeta('name');
  @override
  late final GeneratedColumn<String> name = GeneratedColumn<String>(
    'name',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _therapeuticAreaIdMeta = const VerificationMeta(
    'therapeuticAreaId',
  );
  @override
  late final GeneratedColumn<int> therapeuticAreaId = GeneratedColumn<int>(
    'therapeutic_area_id',
    aliasedName,
    true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    true,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: false,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    name,
    therapeuticAreaId,
    updatedAt,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'products';
  @override
  VerificationContext validateIntegrity(
    Insertable<ProductEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('name')) {
      context.handle(
        _nameMeta,
        name.isAcceptableOrUnknown(data['name']!, _nameMeta),
      );
    } else if (isInserting) {
      context.missing(_nameMeta);
    }
    if (data.containsKey('therapeutic_area_id')) {
      context.handle(
        _therapeuticAreaIdMeta,
        therapeuticAreaId.isAcceptableOrUnknown(
          data['therapeutic_area_id']!,
          _therapeuticAreaIdMeta,
        ),
      );
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  ProductEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return ProductEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      name: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}name'],
      )!,
      therapeuticAreaId: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}therapeutic_area_id'],
      ),
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      ),
    );
  }

  @override
  $ProductsTable createAlias(String alias) {
    return $ProductsTable(attachedDatabase, alias);
  }
}

class ProductEntity extends DataClass implements Insertable<ProductEntity> {
  final int id;
  final String name;
  final int? therapeuticAreaId;
  final DateTime? updatedAt;
  const ProductEntity({
    required this.id,
    required this.name,
    this.therapeuticAreaId,
    this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['name'] = Variable<String>(name);
    if (!nullToAbsent || therapeuticAreaId != null) {
      map['therapeutic_area_id'] = Variable<int>(therapeuticAreaId);
    }
    if (!nullToAbsent || updatedAt != null) {
      map['updated_at'] = Variable<DateTime>(updatedAt);
    }
    return map;
  }

  ProductsCompanion toCompanion(bool nullToAbsent) {
    return ProductsCompanion(
      id: Value(id),
      name: Value(name),
      therapeuticAreaId: therapeuticAreaId == null && nullToAbsent
          ? const Value.absent()
          : Value(therapeuticAreaId),
      updatedAt: updatedAt == null && nullToAbsent
          ? const Value.absent()
          : Value(updatedAt),
    );
  }

  factory ProductEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return ProductEntity(
      id: serializer.fromJson<int>(json['id']),
      name: serializer.fromJson<String>(json['name']),
      therapeuticAreaId: serializer.fromJson<int?>(json['therapeuticAreaId']),
      updatedAt: serializer.fromJson<DateTime?>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'name': serializer.toJson<String>(name),
      'therapeuticAreaId': serializer.toJson<int?>(therapeuticAreaId),
      'updatedAt': serializer.toJson<DateTime?>(updatedAt),
    };
  }

  ProductEntity copyWith({
    int? id,
    String? name,
    Value<int?> therapeuticAreaId = const Value.absent(),
    Value<DateTime?> updatedAt = const Value.absent(),
  }) => ProductEntity(
    id: id ?? this.id,
    name: name ?? this.name,
    therapeuticAreaId: therapeuticAreaId.present
        ? therapeuticAreaId.value
        : this.therapeuticAreaId,
    updatedAt: updatedAt.present ? updatedAt.value : this.updatedAt,
  );
  ProductEntity copyWithCompanion(ProductsCompanion data) {
    return ProductEntity(
      id: data.id.present ? data.id.value : this.id,
      name: data.name.present ? data.name.value : this.name,
      therapeuticAreaId: data.therapeuticAreaId.present
          ? data.therapeuticAreaId.value
          : this.therapeuticAreaId,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('ProductEntity(')
          ..write('id: $id, ')
          ..write('name: $name, ')
          ..write('therapeuticAreaId: $therapeuticAreaId, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, name, therapeuticAreaId, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is ProductEntity &&
          other.id == this.id &&
          other.name == this.name &&
          other.therapeuticAreaId == this.therapeuticAreaId &&
          other.updatedAt == this.updatedAt);
}

class ProductsCompanion extends UpdateCompanion<ProductEntity> {
  final Value<int> id;
  final Value<String> name;
  final Value<int?> therapeuticAreaId;
  final Value<DateTime?> updatedAt;
  const ProductsCompanion({
    this.id = const Value.absent(),
    this.name = const Value.absent(),
    this.therapeuticAreaId = const Value.absent(),
    this.updatedAt = const Value.absent(),
  });
  ProductsCompanion.insert({
    this.id = const Value.absent(),
    required String name,
    this.therapeuticAreaId = const Value.absent(),
    this.updatedAt = const Value.absent(),
  }) : name = Value(name);
  static Insertable<ProductEntity> custom({
    Expression<int>? id,
    Expression<String>? name,
    Expression<int>? therapeuticAreaId,
    Expression<DateTime>? updatedAt,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (name != null) 'name': name,
      if (therapeuticAreaId != null) 'therapeutic_area_id': therapeuticAreaId,
      if (updatedAt != null) 'updated_at': updatedAt,
    });
  }

  ProductsCompanion copyWith({
    Value<int>? id,
    Value<String>? name,
    Value<int?>? therapeuticAreaId,
    Value<DateTime?>? updatedAt,
  }) {
    return ProductsCompanion(
      id: id ?? this.id,
      name: name ?? this.name,
      therapeuticAreaId: therapeuticAreaId ?? this.therapeuticAreaId,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (name.present) {
      map['name'] = Variable<String>(name.value);
    }
    if (therapeuticAreaId.present) {
      map['therapeutic_area_id'] = Variable<int>(therapeuticAreaId.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('ProductsCompanion(')
          ..write('id: $id, ')
          ..write('name: $name, ')
          ..write('therapeuticAreaId: $therapeuticAreaId, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }
}

class $PromotionalInputsTable extends PromotionalInputs
    with TableInfo<$PromotionalInputsTable, PromotionalInputEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $PromotionalInputsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _nameMeta = const VerificationMeta('name');
  @override
  late final GeneratedColumn<String> name = GeneratedColumn<String>(
    'name',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _typeMeta = const VerificationMeta('type');
  @override
  late final GeneratedColumn<String> type = GeneratedColumn<String>(
    'type',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    true,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: false,
  );
  @override
  List<GeneratedColumn> get $columns => [id, name, type, updatedAt];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'promotional_inputs';
  @override
  VerificationContext validateIntegrity(
    Insertable<PromotionalInputEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('name')) {
      context.handle(
        _nameMeta,
        name.isAcceptableOrUnknown(data['name']!, _nameMeta),
      );
    } else if (isInserting) {
      context.missing(_nameMeta);
    }
    if (data.containsKey('type')) {
      context.handle(
        _typeMeta,
        type.isAcceptableOrUnknown(data['type']!, _typeMeta),
      );
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  PromotionalInputEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return PromotionalInputEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      name: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}name'],
      )!,
      type: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}type'],
      ),
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      ),
    );
  }

  @override
  $PromotionalInputsTable createAlias(String alias) {
    return $PromotionalInputsTable(attachedDatabase, alias);
  }
}

class PromotionalInputEntity extends DataClass
    implements Insertable<PromotionalInputEntity> {
  final int id;
  final String name;
  final String? type;
  final DateTime? updatedAt;
  const PromotionalInputEntity({
    required this.id,
    required this.name,
    this.type,
    this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['name'] = Variable<String>(name);
    if (!nullToAbsent || type != null) {
      map['type'] = Variable<String>(type);
    }
    if (!nullToAbsent || updatedAt != null) {
      map['updated_at'] = Variable<DateTime>(updatedAt);
    }
    return map;
  }

  PromotionalInputsCompanion toCompanion(bool nullToAbsent) {
    return PromotionalInputsCompanion(
      id: Value(id),
      name: Value(name),
      type: type == null && nullToAbsent ? const Value.absent() : Value(type),
      updatedAt: updatedAt == null && nullToAbsent
          ? const Value.absent()
          : Value(updatedAt),
    );
  }

  factory PromotionalInputEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return PromotionalInputEntity(
      id: serializer.fromJson<int>(json['id']),
      name: serializer.fromJson<String>(json['name']),
      type: serializer.fromJson<String?>(json['type']),
      updatedAt: serializer.fromJson<DateTime?>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'name': serializer.toJson<String>(name),
      'type': serializer.toJson<String?>(type),
      'updatedAt': serializer.toJson<DateTime?>(updatedAt),
    };
  }

  PromotionalInputEntity copyWith({
    int? id,
    String? name,
    Value<String?> type = const Value.absent(),
    Value<DateTime?> updatedAt = const Value.absent(),
  }) => PromotionalInputEntity(
    id: id ?? this.id,
    name: name ?? this.name,
    type: type.present ? type.value : this.type,
    updatedAt: updatedAt.present ? updatedAt.value : this.updatedAt,
  );
  PromotionalInputEntity copyWithCompanion(PromotionalInputsCompanion data) {
    return PromotionalInputEntity(
      id: data.id.present ? data.id.value : this.id,
      name: data.name.present ? data.name.value : this.name,
      type: data.type.present ? data.type.value : this.type,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('PromotionalInputEntity(')
          ..write('id: $id, ')
          ..write('name: $name, ')
          ..write('type: $type, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, name, type, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is PromotionalInputEntity &&
          other.id == this.id &&
          other.name == this.name &&
          other.type == this.type &&
          other.updatedAt == this.updatedAt);
}

class PromotionalInputsCompanion
    extends UpdateCompanion<PromotionalInputEntity> {
  final Value<int> id;
  final Value<String> name;
  final Value<String?> type;
  final Value<DateTime?> updatedAt;
  const PromotionalInputsCompanion({
    this.id = const Value.absent(),
    this.name = const Value.absent(),
    this.type = const Value.absent(),
    this.updatedAt = const Value.absent(),
  });
  PromotionalInputsCompanion.insert({
    this.id = const Value.absent(),
    required String name,
    this.type = const Value.absent(),
    this.updatedAt = const Value.absent(),
  }) : name = Value(name);
  static Insertable<PromotionalInputEntity> custom({
    Expression<int>? id,
    Expression<String>? name,
    Expression<String>? type,
    Expression<DateTime>? updatedAt,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (name != null) 'name': name,
      if (type != null) 'type': type,
      if (updatedAt != null) 'updated_at': updatedAt,
    });
  }

  PromotionalInputsCompanion copyWith({
    Value<int>? id,
    Value<String>? name,
    Value<String?>? type,
    Value<DateTime?>? updatedAt,
  }) {
    return PromotionalInputsCompanion(
      id: id ?? this.id,
      name: name ?? this.name,
      type: type ?? this.type,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (name.present) {
      map['name'] = Variable<String>(name.value);
    }
    if (type.present) {
      map['type'] = Variable<String>(type.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('PromotionalInputsCompanion(')
          ..write('id: $id, ')
          ..write('name: $name, ')
          ..write('type: $type, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }
}

class $DcrsTable extends Dcrs with TableInfo<$DcrsTable, DcrEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $DcrsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _uuidMeta = const VerificationMeta('uuid');
  @override
  late final GeneratedColumn<String> uuid = GeneratedColumn<String>(
    'uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dateMeta = const VerificationMeta('date');
  @override
  late final GeneratedColumn<DateTime> date = GeneratedColumn<DateTime>(
    'date',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _doctorUuidMeta = const VerificationMeta(
    'doctorUuid',
  );
  @override
  late final GeneratedColumn<String> doctorUuid = GeneratedColumn<String>(
    'doctor_uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _doctorIdMeta = const VerificationMeta(
    'doctorId',
  );
  @override
  late final GeneratedColumn<int> doctorId = GeneratedColumn<int>(
    'doctor_id',
    aliasedName,
    true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _doctorNameMeta = const VerificationMeta(
    'doctorName',
  );
  @override
  late final GeneratedColumn<String> doctorName = GeneratedColumn<String>(
    'doctor_name',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _remarksMeta = const VerificationMeta(
    'remarks',
  );
  @override
  late final GeneratedColumn<String> remarks = GeneratedColumn<String>(
    'remarks',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _latitudeMeta = const VerificationMeta(
    'latitude',
  );
  @override
  late final GeneratedColumn<double> latitude = GeneratedColumn<double>(
    'latitude',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _longitudeMeta = const VerificationMeta(
    'longitude',
  );
  @override
  late final GeneratedColumn<double> longitude = GeneratedColumn<double>(
    'longitude',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _signatureSvgMeta = const VerificationMeta(
    'signatureSvg',
  );
  @override
  late final GeneratedColumn<String> signatureSvg = GeneratedColumn<String>(
    'signature_svg',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _syncStatusMeta = const VerificationMeta(
    'syncStatus',
  );
  @override
  late final GeneratedColumn<String> syncStatus = GeneratedColumn<String>(
    'sync_status',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('pending'),
  );
  static const VerificationMeta _createdAtMeta = const VerificationMeta(
    'createdAt',
  );
  @override
  late final GeneratedColumn<DateTime> createdAt = GeneratedColumn<DateTime>(
    'created_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    uuid,
    date,
    doctorUuid,
    doctorId,
    doctorName,
    remarks,
    latitude,
    longitude,
    signatureSvg,
    syncStatus,
    createdAt,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'dcrs';
  @override
  VerificationContext validateIntegrity(
    Insertable<DcrEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('uuid')) {
      context.handle(
        _uuidMeta,
        uuid.isAcceptableOrUnknown(data['uuid']!, _uuidMeta),
      );
    } else if (isInserting) {
      context.missing(_uuidMeta);
    }
    if (data.containsKey('date')) {
      context.handle(
        _dateMeta,
        date.isAcceptableOrUnknown(data['date']!, _dateMeta),
      );
    } else if (isInserting) {
      context.missing(_dateMeta);
    }
    if (data.containsKey('doctor_uuid')) {
      context.handle(
        _doctorUuidMeta,
        doctorUuid.isAcceptableOrUnknown(data['doctor_uuid']!, _doctorUuidMeta),
      );
    } else if (isInserting) {
      context.missing(_doctorUuidMeta);
    }
    if (data.containsKey('doctor_id')) {
      context.handle(
        _doctorIdMeta,
        doctorId.isAcceptableOrUnknown(data['doctor_id']!, _doctorIdMeta),
      );
    }
    if (data.containsKey('doctor_name')) {
      context.handle(
        _doctorNameMeta,
        doctorName.isAcceptableOrUnknown(data['doctor_name']!, _doctorNameMeta),
      );
    }
    if (data.containsKey('remarks')) {
      context.handle(
        _remarksMeta,
        remarks.isAcceptableOrUnknown(data['remarks']!, _remarksMeta),
      );
    }
    if (data.containsKey('latitude')) {
      context.handle(
        _latitudeMeta,
        latitude.isAcceptableOrUnknown(data['latitude']!, _latitudeMeta),
      );
    }
    if (data.containsKey('longitude')) {
      context.handle(
        _longitudeMeta,
        longitude.isAcceptableOrUnknown(data['longitude']!, _longitudeMeta),
      );
    }
    if (data.containsKey('signature_svg')) {
      context.handle(
        _signatureSvgMeta,
        signatureSvg.isAcceptableOrUnknown(
          data['signature_svg']!,
          _signatureSvgMeta,
        ),
      );
    }
    if (data.containsKey('sync_status')) {
      context.handle(
        _syncStatusMeta,
        syncStatus.isAcceptableOrUnknown(data['sync_status']!, _syncStatusMeta),
      );
    }
    if (data.containsKey('created_at')) {
      context.handle(
        _createdAtMeta,
        createdAt.isAcceptableOrUnknown(data['created_at']!, _createdAtMeta),
      );
    } else if (isInserting) {
      context.missing(_createdAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {uuid};
  @override
  DcrEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return DcrEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      ),
      uuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}uuid'],
      )!,
      date: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}date'],
      )!,
      doctorUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}doctor_uuid'],
      )!,
      doctorId: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}doctor_id'],
      ),
      doctorName: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}doctor_name'],
      ),
      remarks: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}remarks'],
      ),
      latitude: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}latitude'],
      ),
      longitude: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}longitude'],
      ),
      signatureSvg: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}signature_svg'],
      ),
      syncStatus: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}sync_status'],
      )!,
      createdAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}created_at'],
      )!,
    );
  }

  @override
  $DcrsTable createAlias(String alias) {
    return $DcrsTable(attachedDatabase, alias);
  }
}

class DcrEntity extends DataClass implements Insertable<DcrEntity> {
  final int? id;
  final String uuid;
  final DateTime date;
  final String doctorUuid;
  final int? doctorId;
  final String? doctorName;
  final String? remarks;
  final double? latitude;
  final double? longitude;
  final String? signatureSvg;
  final String syncStatus;
  final DateTime createdAt;
  const DcrEntity({
    this.id,
    required this.uuid,
    required this.date,
    required this.doctorUuid,
    this.doctorId,
    this.doctorName,
    this.remarks,
    this.latitude,
    this.longitude,
    this.signatureSvg,
    required this.syncStatus,
    required this.createdAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (!nullToAbsent || id != null) {
      map['id'] = Variable<int>(id);
    }
    map['uuid'] = Variable<String>(uuid);
    map['date'] = Variable<DateTime>(date);
    map['doctor_uuid'] = Variable<String>(doctorUuid);
    if (!nullToAbsent || doctorId != null) {
      map['doctor_id'] = Variable<int>(doctorId);
    }
    if (!nullToAbsent || doctorName != null) {
      map['doctor_name'] = Variable<String>(doctorName);
    }
    if (!nullToAbsent || remarks != null) {
      map['remarks'] = Variable<String>(remarks);
    }
    if (!nullToAbsent || latitude != null) {
      map['latitude'] = Variable<double>(latitude);
    }
    if (!nullToAbsent || longitude != null) {
      map['longitude'] = Variable<double>(longitude);
    }
    if (!nullToAbsent || signatureSvg != null) {
      map['signature_svg'] = Variable<String>(signatureSvg);
    }
    map['sync_status'] = Variable<String>(syncStatus);
    map['created_at'] = Variable<DateTime>(createdAt);
    return map;
  }

  DcrsCompanion toCompanion(bool nullToAbsent) {
    return DcrsCompanion(
      id: id == null && nullToAbsent ? const Value.absent() : Value(id),
      uuid: Value(uuid),
      date: Value(date),
      doctorUuid: Value(doctorUuid),
      doctorId: doctorId == null && nullToAbsent
          ? const Value.absent()
          : Value(doctorId),
      doctorName: doctorName == null && nullToAbsent
          ? const Value.absent()
          : Value(doctorName),
      remarks: remarks == null && nullToAbsent
          ? const Value.absent()
          : Value(remarks),
      latitude: latitude == null && nullToAbsent
          ? const Value.absent()
          : Value(latitude),
      longitude: longitude == null && nullToAbsent
          ? const Value.absent()
          : Value(longitude),
      signatureSvg: signatureSvg == null && nullToAbsent
          ? const Value.absent()
          : Value(signatureSvg),
      syncStatus: Value(syncStatus),
      createdAt: Value(createdAt),
    );
  }

  factory DcrEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return DcrEntity(
      id: serializer.fromJson<int?>(json['id']),
      uuid: serializer.fromJson<String>(json['uuid']),
      date: serializer.fromJson<DateTime>(json['date']),
      doctorUuid: serializer.fromJson<String>(json['doctorUuid']),
      doctorId: serializer.fromJson<int?>(json['doctorId']),
      doctorName: serializer.fromJson<String?>(json['doctorName']),
      remarks: serializer.fromJson<String?>(json['remarks']),
      latitude: serializer.fromJson<double?>(json['latitude']),
      longitude: serializer.fromJson<double?>(json['longitude']),
      signatureSvg: serializer.fromJson<String?>(json['signatureSvg']),
      syncStatus: serializer.fromJson<String>(json['syncStatus']),
      createdAt: serializer.fromJson<DateTime>(json['createdAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int?>(id),
      'uuid': serializer.toJson<String>(uuid),
      'date': serializer.toJson<DateTime>(date),
      'doctorUuid': serializer.toJson<String>(doctorUuid),
      'doctorId': serializer.toJson<int?>(doctorId),
      'doctorName': serializer.toJson<String?>(doctorName),
      'remarks': serializer.toJson<String?>(remarks),
      'latitude': serializer.toJson<double?>(latitude),
      'longitude': serializer.toJson<double?>(longitude),
      'signatureSvg': serializer.toJson<String?>(signatureSvg),
      'syncStatus': serializer.toJson<String>(syncStatus),
      'createdAt': serializer.toJson<DateTime>(createdAt),
    };
  }

  DcrEntity copyWith({
    Value<int?> id = const Value.absent(),
    String? uuid,
    DateTime? date,
    String? doctorUuid,
    Value<int?> doctorId = const Value.absent(),
    Value<String?> doctorName = const Value.absent(),
    Value<String?> remarks = const Value.absent(),
    Value<double?> latitude = const Value.absent(),
    Value<double?> longitude = const Value.absent(),
    Value<String?> signatureSvg = const Value.absent(),
    String? syncStatus,
    DateTime? createdAt,
  }) => DcrEntity(
    id: id.present ? id.value : this.id,
    uuid: uuid ?? this.uuid,
    date: date ?? this.date,
    doctorUuid: doctorUuid ?? this.doctorUuid,
    doctorId: doctorId.present ? doctorId.value : this.doctorId,
    doctorName: doctorName.present ? doctorName.value : this.doctorName,
    remarks: remarks.present ? remarks.value : this.remarks,
    latitude: latitude.present ? latitude.value : this.latitude,
    longitude: longitude.present ? longitude.value : this.longitude,
    signatureSvg: signatureSvg.present ? signatureSvg.value : this.signatureSvg,
    syncStatus: syncStatus ?? this.syncStatus,
    createdAt: createdAt ?? this.createdAt,
  );
  DcrEntity copyWithCompanion(DcrsCompanion data) {
    return DcrEntity(
      id: data.id.present ? data.id.value : this.id,
      uuid: data.uuid.present ? data.uuid.value : this.uuid,
      date: data.date.present ? data.date.value : this.date,
      doctorUuid: data.doctorUuid.present
          ? data.doctorUuid.value
          : this.doctorUuid,
      doctorId: data.doctorId.present ? data.doctorId.value : this.doctorId,
      doctorName: data.doctorName.present
          ? data.doctorName.value
          : this.doctorName,
      remarks: data.remarks.present ? data.remarks.value : this.remarks,
      latitude: data.latitude.present ? data.latitude.value : this.latitude,
      longitude: data.longitude.present ? data.longitude.value : this.longitude,
      signatureSvg: data.signatureSvg.present
          ? data.signatureSvg.value
          : this.signatureSvg,
      syncStatus: data.syncStatus.present
          ? data.syncStatus.value
          : this.syncStatus,
      createdAt: data.createdAt.present ? data.createdAt.value : this.createdAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('DcrEntity(')
          ..write('id: $id, ')
          ..write('uuid: $uuid, ')
          ..write('date: $date, ')
          ..write('doctorUuid: $doctorUuid, ')
          ..write('doctorId: $doctorId, ')
          ..write('doctorName: $doctorName, ')
          ..write('remarks: $remarks, ')
          ..write('latitude: $latitude, ')
          ..write('longitude: $longitude, ')
          ..write('signatureSvg: $signatureSvg, ')
          ..write('syncStatus: $syncStatus, ')
          ..write('createdAt: $createdAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    uuid,
    date,
    doctorUuid,
    doctorId,
    doctorName,
    remarks,
    latitude,
    longitude,
    signatureSvg,
    syncStatus,
    createdAt,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is DcrEntity &&
          other.id == this.id &&
          other.uuid == this.uuid &&
          other.date == this.date &&
          other.doctorUuid == this.doctorUuid &&
          other.doctorId == this.doctorId &&
          other.doctorName == this.doctorName &&
          other.remarks == this.remarks &&
          other.latitude == this.latitude &&
          other.longitude == this.longitude &&
          other.signatureSvg == this.signatureSvg &&
          other.syncStatus == this.syncStatus &&
          other.createdAt == this.createdAt);
}

class DcrsCompanion extends UpdateCompanion<DcrEntity> {
  final Value<int?> id;
  final Value<String> uuid;
  final Value<DateTime> date;
  final Value<String> doctorUuid;
  final Value<int?> doctorId;
  final Value<String?> doctorName;
  final Value<String?> remarks;
  final Value<double?> latitude;
  final Value<double?> longitude;
  final Value<String?> signatureSvg;
  final Value<String> syncStatus;
  final Value<DateTime> createdAt;
  final Value<int> rowid;
  const DcrsCompanion({
    this.id = const Value.absent(),
    this.uuid = const Value.absent(),
    this.date = const Value.absent(),
    this.doctorUuid = const Value.absent(),
    this.doctorId = const Value.absent(),
    this.doctorName = const Value.absent(),
    this.remarks = const Value.absent(),
    this.latitude = const Value.absent(),
    this.longitude = const Value.absent(),
    this.signatureSvg = const Value.absent(),
    this.syncStatus = const Value.absent(),
    this.createdAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  DcrsCompanion.insert({
    this.id = const Value.absent(),
    required String uuid,
    required DateTime date,
    required String doctorUuid,
    this.doctorId = const Value.absent(),
    this.doctorName = const Value.absent(),
    this.remarks = const Value.absent(),
    this.latitude = const Value.absent(),
    this.longitude = const Value.absent(),
    this.signatureSvg = const Value.absent(),
    this.syncStatus = const Value.absent(),
    required DateTime createdAt,
    this.rowid = const Value.absent(),
  }) : uuid = Value(uuid),
       date = Value(date),
       doctorUuid = Value(doctorUuid),
       createdAt = Value(createdAt);
  static Insertable<DcrEntity> custom({
    Expression<int>? id,
    Expression<String>? uuid,
    Expression<DateTime>? date,
    Expression<String>? doctorUuid,
    Expression<int>? doctorId,
    Expression<String>? doctorName,
    Expression<String>? remarks,
    Expression<double>? latitude,
    Expression<double>? longitude,
    Expression<String>? signatureSvg,
    Expression<String>? syncStatus,
    Expression<DateTime>? createdAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (uuid != null) 'uuid': uuid,
      if (date != null) 'date': date,
      if (doctorUuid != null) 'doctor_uuid': doctorUuid,
      if (doctorId != null) 'doctor_id': doctorId,
      if (doctorName != null) 'doctor_name': doctorName,
      if (remarks != null) 'remarks': remarks,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
      if (signatureSvg != null) 'signature_svg': signatureSvg,
      if (syncStatus != null) 'sync_status': syncStatus,
      if (createdAt != null) 'created_at': createdAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  DcrsCompanion copyWith({
    Value<int?>? id,
    Value<String>? uuid,
    Value<DateTime>? date,
    Value<String>? doctorUuid,
    Value<int?>? doctorId,
    Value<String?>? doctorName,
    Value<String?>? remarks,
    Value<double?>? latitude,
    Value<double?>? longitude,
    Value<String?>? signatureSvg,
    Value<String>? syncStatus,
    Value<DateTime>? createdAt,
    Value<int>? rowid,
  }) {
    return DcrsCompanion(
      id: id ?? this.id,
      uuid: uuid ?? this.uuid,
      date: date ?? this.date,
      doctorUuid: doctorUuid ?? this.doctorUuid,
      doctorId: doctorId ?? this.doctorId,
      doctorName: doctorName ?? this.doctorName,
      remarks: remarks ?? this.remarks,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      signatureSvg: signatureSvg ?? this.signatureSvg,
      syncStatus: syncStatus ?? this.syncStatus,
      createdAt: createdAt ?? this.createdAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (uuid.present) {
      map['uuid'] = Variable<String>(uuid.value);
    }
    if (date.present) {
      map['date'] = Variable<DateTime>(date.value);
    }
    if (doctorUuid.present) {
      map['doctor_uuid'] = Variable<String>(doctorUuid.value);
    }
    if (doctorId.present) {
      map['doctor_id'] = Variable<int>(doctorId.value);
    }
    if (doctorName.present) {
      map['doctor_name'] = Variable<String>(doctorName.value);
    }
    if (remarks.present) {
      map['remarks'] = Variable<String>(remarks.value);
    }
    if (latitude.present) {
      map['latitude'] = Variable<double>(latitude.value);
    }
    if (longitude.present) {
      map['longitude'] = Variable<double>(longitude.value);
    }
    if (signatureSvg.present) {
      map['signature_svg'] = Variable<String>(signatureSvg.value);
    }
    if (syncStatus.present) {
      map['sync_status'] = Variable<String>(syncStatus.value);
    }
    if (createdAt.present) {
      map['created_at'] = Variable<DateTime>(createdAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('DcrsCompanion(')
          ..write('id: $id, ')
          ..write('uuid: $uuid, ')
          ..write('date: $date, ')
          ..write('doctorUuid: $doctorUuid, ')
          ..write('doctorId: $doctorId, ')
          ..write('doctorName: $doctorName, ')
          ..write('remarks: $remarks, ')
          ..write('latitude: $latitude, ')
          ..write('longitude: $longitude, ')
          ..write('signatureSvg: $signatureSvg, ')
          ..write('syncStatus: $syncStatus, ')
          ..write('createdAt: $createdAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $DcrProductsTable extends DcrProducts
    with TableInfo<$DcrProductsTable, DcrProductEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $DcrProductsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    hasAutoIncrement: true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultConstraints: GeneratedColumn.constraintIsAlways(
      'PRIMARY KEY AUTOINCREMENT',
    ),
  );
  static const VerificationMeta _dcrUuidMeta = const VerificationMeta(
    'dcrUuid',
  );
  @override
  late final GeneratedColumn<String> dcrUuid = GeneratedColumn<String>(
    'dcr_uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _productIdMeta = const VerificationMeta(
    'productId',
  );
  @override
  late final GeneratedColumn<int> productId = GeneratedColumn<int>(
    'product_id',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _quantityMeta = const VerificationMeta(
    'quantity',
  );
  @override
  late final GeneratedColumn<int> quantity = GeneratedColumn<int>(
    'quantity',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultValue: const Constant(1),
  );
  @override
  List<GeneratedColumn> get $columns => [id, dcrUuid, productId, quantity];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'dcr_products';
  @override
  VerificationContext validateIntegrity(
    Insertable<DcrProductEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('dcr_uuid')) {
      context.handle(
        _dcrUuidMeta,
        dcrUuid.isAcceptableOrUnknown(data['dcr_uuid']!, _dcrUuidMeta),
      );
    } else if (isInserting) {
      context.missing(_dcrUuidMeta);
    }
    if (data.containsKey('product_id')) {
      context.handle(
        _productIdMeta,
        productId.isAcceptableOrUnknown(data['product_id']!, _productIdMeta),
      );
    } else if (isInserting) {
      context.missing(_productIdMeta);
    }
    if (data.containsKey('quantity')) {
      context.handle(
        _quantityMeta,
        quantity.isAcceptableOrUnknown(data['quantity']!, _quantityMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  DcrProductEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return DcrProductEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      dcrUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}dcr_uuid'],
      )!,
      productId: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}product_id'],
      )!,
      quantity: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}quantity'],
      )!,
    );
  }

  @override
  $DcrProductsTable createAlias(String alias) {
    return $DcrProductsTable(attachedDatabase, alias);
  }
}

class DcrProductEntity extends DataClass
    implements Insertable<DcrProductEntity> {
  final int id;
  final String dcrUuid;
  final int productId;
  final int quantity;
  const DcrProductEntity({
    required this.id,
    required this.dcrUuid,
    required this.productId,
    required this.quantity,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['dcr_uuid'] = Variable<String>(dcrUuid);
    map['product_id'] = Variable<int>(productId);
    map['quantity'] = Variable<int>(quantity);
    return map;
  }

  DcrProductsCompanion toCompanion(bool nullToAbsent) {
    return DcrProductsCompanion(
      id: Value(id),
      dcrUuid: Value(dcrUuid),
      productId: Value(productId),
      quantity: Value(quantity),
    );
  }

  factory DcrProductEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return DcrProductEntity(
      id: serializer.fromJson<int>(json['id']),
      dcrUuid: serializer.fromJson<String>(json['dcrUuid']),
      productId: serializer.fromJson<int>(json['productId']),
      quantity: serializer.fromJson<int>(json['quantity']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'dcrUuid': serializer.toJson<String>(dcrUuid),
      'productId': serializer.toJson<int>(productId),
      'quantity': serializer.toJson<int>(quantity),
    };
  }

  DcrProductEntity copyWith({
    int? id,
    String? dcrUuid,
    int? productId,
    int? quantity,
  }) => DcrProductEntity(
    id: id ?? this.id,
    dcrUuid: dcrUuid ?? this.dcrUuid,
    productId: productId ?? this.productId,
    quantity: quantity ?? this.quantity,
  );
  DcrProductEntity copyWithCompanion(DcrProductsCompanion data) {
    return DcrProductEntity(
      id: data.id.present ? data.id.value : this.id,
      dcrUuid: data.dcrUuid.present ? data.dcrUuid.value : this.dcrUuid,
      productId: data.productId.present ? data.productId.value : this.productId,
      quantity: data.quantity.present ? data.quantity.value : this.quantity,
    );
  }

  @override
  String toString() {
    return (StringBuffer('DcrProductEntity(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('productId: $productId, ')
          ..write('quantity: $quantity')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dcrUuid, productId, quantity);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is DcrProductEntity &&
          other.id == this.id &&
          other.dcrUuid == this.dcrUuid &&
          other.productId == this.productId &&
          other.quantity == this.quantity);
}

class DcrProductsCompanion extends UpdateCompanion<DcrProductEntity> {
  final Value<int> id;
  final Value<String> dcrUuid;
  final Value<int> productId;
  final Value<int> quantity;
  const DcrProductsCompanion({
    this.id = const Value.absent(),
    this.dcrUuid = const Value.absent(),
    this.productId = const Value.absent(),
    this.quantity = const Value.absent(),
  });
  DcrProductsCompanion.insert({
    this.id = const Value.absent(),
    required String dcrUuid,
    required int productId,
    this.quantity = const Value.absent(),
  }) : dcrUuid = Value(dcrUuid),
       productId = Value(productId);
  static Insertable<DcrProductEntity> custom({
    Expression<int>? id,
    Expression<String>? dcrUuid,
    Expression<int>? productId,
    Expression<int>? quantity,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dcrUuid != null) 'dcr_uuid': dcrUuid,
      if (productId != null) 'product_id': productId,
      if (quantity != null) 'quantity': quantity,
    });
  }

  DcrProductsCompanion copyWith({
    Value<int>? id,
    Value<String>? dcrUuid,
    Value<int>? productId,
    Value<int>? quantity,
  }) {
    return DcrProductsCompanion(
      id: id ?? this.id,
      dcrUuid: dcrUuid ?? this.dcrUuid,
      productId: productId ?? this.productId,
      quantity: quantity ?? this.quantity,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (dcrUuid.present) {
      map['dcr_uuid'] = Variable<String>(dcrUuid.value);
    }
    if (productId.present) {
      map['product_id'] = Variable<int>(productId.value);
    }
    if (quantity.present) {
      map['quantity'] = Variable<int>(quantity.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('DcrProductsCompanion(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('productId: $productId, ')
          ..write('quantity: $quantity')
          ..write(')'))
        .toString();
  }
}

class $DcrInputsTable extends DcrInputs
    with TableInfo<$DcrInputsTable, DcrInputEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $DcrInputsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    hasAutoIncrement: true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultConstraints: GeneratedColumn.constraintIsAlways(
      'PRIMARY KEY AUTOINCREMENT',
    ),
  );
  static const VerificationMeta _dcrUuidMeta = const VerificationMeta(
    'dcrUuid',
  );
  @override
  late final GeneratedColumn<String> dcrUuid = GeneratedColumn<String>(
    'dcr_uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _promotionalInputIdMeta =
      const VerificationMeta('promotionalInputId');
  @override
  late final GeneratedColumn<int> promotionalInputId = GeneratedColumn<int>(
    'promotional_input_id',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _quantityMeta = const VerificationMeta(
    'quantity',
  );
  @override
  late final GeneratedColumn<int> quantity = GeneratedColumn<int>(
    'quantity',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultValue: const Constant(1),
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    dcrUuid,
    promotionalInputId,
    quantity,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'dcr_inputs';
  @override
  VerificationContext validateIntegrity(
    Insertable<DcrInputEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('dcr_uuid')) {
      context.handle(
        _dcrUuidMeta,
        dcrUuid.isAcceptableOrUnknown(data['dcr_uuid']!, _dcrUuidMeta),
      );
    } else if (isInserting) {
      context.missing(_dcrUuidMeta);
    }
    if (data.containsKey('promotional_input_id')) {
      context.handle(
        _promotionalInputIdMeta,
        promotionalInputId.isAcceptableOrUnknown(
          data['promotional_input_id']!,
          _promotionalInputIdMeta,
        ),
      );
    } else if (isInserting) {
      context.missing(_promotionalInputIdMeta);
    }
    if (data.containsKey('quantity')) {
      context.handle(
        _quantityMeta,
        quantity.isAcceptableOrUnknown(data['quantity']!, _quantityMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  DcrInputEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return DcrInputEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      dcrUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}dcr_uuid'],
      )!,
      promotionalInputId: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}promotional_input_id'],
      )!,
      quantity: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}quantity'],
      )!,
    );
  }

  @override
  $DcrInputsTable createAlias(String alias) {
    return $DcrInputsTable(attachedDatabase, alias);
  }
}

class DcrInputEntity extends DataClass implements Insertable<DcrInputEntity> {
  final int id;
  final String dcrUuid;
  final int promotionalInputId;
  final int quantity;
  const DcrInputEntity({
    required this.id,
    required this.dcrUuid,
    required this.promotionalInputId,
    required this.quantity,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['dcr_uuid'] = Variable<String>(dcrUuid);
    map['promotional_input_id'] = Variable<int>(promotionalInputId);
    map['quantity'] = Variable<int>(quantity);
    return map;
  }

  DcrInputsCompanion toCompanion(bool nullToAbsent) {
    return DcrInputsCompanion(
      id: Value(id),
      dcrUuid: Value(dcrUuid),
      promotionalInputId: Value(promotionalInputId),
      quantity: Value(quantity),
    );
  }

  factory DcrInputEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return DcrInputEntity(
      id: serializer.fromJson<int>(json['id']),
      dcrUuid: serializer.fromJson<String>(json['dcrUuid']),
      promotionalInputId: serializer.fromJson<int>(json['promotionalInputId']),
      quantity: serializer.fromJson<int>(json['quantity']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'dcrUuid': serializer.toJson<String>(dcrUuid),
      'promotionalInputId': serializer.toJson<int>(promotionalInputId),
      'quantity': serializer.toJson<int>(quantity),
    };
  }

  DcrInputEntity copyWith({
    int? id,
    String? dcrUuid,
    int? promotionalInputId,
    int? quantity,
  }) => DcrInputEntity(
    id: id ?? this.id,
    dcrUuid: dcrUuid ?? this.dcrUuid,
    promotionalInputId: promotionalInputId ?? this.promotionalInputId,
    quantity: quantity ?? this.quantity,
  );
  DcrInputEntity copyWithCompanion(DcrInputsCompanion data) {
    return DcrInputEntity(
      id: data.id.present ? data.id.value : this.id,
      dcrUuid: data.dcrUuid.present ? data.dcrUuid.value : this.dcrUuid,
      promotionalInputId: data.promotionalInputId.present
          ? data.promotionalInputId.value
          : this.promotionalInputId,
      quantity: data.quantity.present ? data.quantity.value : this.quantity,
    );
  }

  @override
  String toString() {
    return (StringBuffer('DcrInputEntity(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('promotionalInputId: $promotionalInputId, ')
          ..write('quantity: $quantity')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dcrUuid, promotionalInputId, quantity);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is DcrInputEntity &&
          other.id == this.id &&
          other.dcrUuid == this.dcrUuid &&
          other.promotionalInputId == this.promotionalInputId &&
          other.quantity == this.quantity);
}

class DcrInputsCompanion extends UpdateCompanion<DcrInputEntity> {
  final Value<int> id;
  final Value<String> dcrUuid;
  final Value<int> promotionalInputId;
  final Value<int> quantity;
  const DcrInputsCompanion({
    this.id = const Value.absent(),
    this.dcrUuid = const Value.absent(),
    this.promotionalInputId = const Value.absent(),
    this.quantity = const Value.absent(),
  });
  DcrInputsCompanion.insert({
    this.id = const Value.absent(),
    required String dcrUuid,
    required int promotionalInputId,
    this.quantity = const Value.absent(),
  }) : dcrUuid = Value(dcrUuid),
       promotionalInputId = Value(promotionalInputId);
  static Insertable<DcrInputEntity> custom({
    Expression<int>? id,
    Expression<String>? dcrUuid,
    Expression<int>? promotionalInputId,
    Expression<int>? quantity,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dcrUuid != null) 'dcr_uuid': dcrUuid,
      if (promotionalInputId != null)
        'promotional_input_id': promotionalInputId,
      if (quantity != null) 'quantity': quantity,
    });
  }

  DcrInputsCompanion copyWith({
    Value<int>? id,
    Value<String>? dcrUuid,
    Value<int>? promotionalInputId,
    Value<int>? quantity,
  }) {
    return DcrInputsCompanion(
      id: id ?? this.id,
      dcrUuid: dcrUuid ?? this.dcrUuid,
      promotionalInputId: promotionalInputId ?? this.promotionalInputId,
      quantity: quantity ?? this.quantity,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (dcrUuid.present) {
      map['dcr_uuid'] = Variable<String>(dcrUuid.value);
    }
    if (promotionalInputId.present) {
      map['promotional_input_id'] = Variable<int>(promotionalInputId.value);
    }
    if (quantity.present) {
      map['quantity'] = Variable<int>(quantity.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('DcrInputsCompanion(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('promotionalInputId: $promotionalInputId, ')
          ..write('quantity: $quantity')
          ..write(')'))
        .toString();
  }
}

class $SlideAnalyticsTable extends SlideAnalytics
    with TableInfo<$SlideAnalyticsTable, SlideAnalyticEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $SlideAnalyticsTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    hasAutoIncrement: true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultConstraints: GeneratedColumn.constraintIsAlways(
      'PRIMARY KEY AUTOINCREMENT',
    ),
  );
  static const VerificationMeta _dcrUuidMeta = const VerificationMeta(
    'dcrUuid',
  );
  @override
  late final GeneratedColumn<String> dcrUuid = GeneratedColumn<String>(
    'dcr_uuid',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _doctorUuidMeta = const VerificationMeta(
    'doctorUuid',
  );
  @override
  late final GeneratedColumn<String> doctorUuid = GeneratedColumn<String>(
    'doctor_uuid',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _slideIndexMeta = const VerificationMeta(
    'slideIndex',
  );
  @override
  late final GeneratedColumn<int> slideIndex = GeneratedColumn<int>(
    'slide_index',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _durationSecondsMeta = const VerificationMeta(
    'durationSeconds',
  );
  @override
  late final GeneratedColumn<int> durationSeconds = GeneratedColumn<int>(
    'duration_seconds',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _timestampMeta = const VerificationMeta(
    'timestamp',
  );
  @override
  late final GeneratedColumn<DateTime> timestamp = GeneratedColumn<DateTime>(
    'timestamp',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _syncStatusMeta = const VerificationMeta(
    'syncStatus',
  );
  @override
  late final GeneratedColumn<String> syncStatus = GeneratedColumn<String>(
    'sync_status',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('pending'),
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    dcrUuid,
    doctorUuid,
    slideIndex,
    durationSeconds,
    timestamp,
    syncStatus,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'slide_analytics';
  @override
  VerificationContext validateIntegrity(
    Insertable<SlideAnalyticEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('dcr_uuid')) {
      context.handle(
        _dcrUuidMeta,
        dcrUuid.isAcceptableOrUnknown(data['dcr_uuid']!, _dcrUuidMeta),
      );
    }
    if (data.containsKey('doctor_uuid')) {
      context.handle(
        _doctorUuidMeta,
        doctorUuid.isAcceptableOrUnknown(data['doctor_uuid']!, _doctorUuidMeta),
      );
    }
    if (data.containsKey('slide_index')) {
      context.handle(
        _slideIndexMeta,
        slideIndex.isAcceptableOrUnknown(data['slide_index']!, _slideIndexMeta),
      );
    } else if (isInserting) {
      context.missing(_slideIndexMeta);
    }
    if (data.containsKey('duration_seconds')) {
      context.handle(
        _durationSecondsMeta,
        durationSeconds.isAcceptableOrUnknown(
          data['duration_seconds']!,
          _durationSecondsMeta,
        ),
      );
    } else if (isInserting) {
      context.missing(_durationSecondsMeta);
    }
    if (data.containsKey('timestamp')) {
      context.handle(
        _timestampMeta,
        timestamp.isAcceptableOrUnknown(data['timestamp']!, _timestampMeta),
      );
    } else if (isInserting) {
      context.missing(_timestampMeta);
    }
    if (data.containsKey('sync_status')) {
      context.handle(
        _syncStatusMeta,
        syncStatus.isAcceptableOrUnknown(data['sync_status']!, _syncStatusMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  SlideAnalyticEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return SlideAnalyticEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      dcrUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}dcr_uuid'],
      ),
      doctorUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}doctor_uuid'],
      ),
      slideIndex: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}slide_index'],
      )!,
      durationSeconds: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}duration_seconds'],
      )!,
      timestamp: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}timestamp'],
      )!,
      syncStatus: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}sync_status'],
      )!,
    );
  }

  @override
  $SlideAnalyticsTable createAlias(String alias) {
    return $SlideAnalyticsTable(attachedDatabase, alias);
  }
}

class SlideAnalyticEntity extends DataClass
    implements Insertable<SlideAnalyticEntity> {
  final int id;
  final String? dcrUuid;
  final String? doctorUuid;
  final int slideIndex;
  final int durationSeconds;
  final DateTime timestamp;
  final String syncStatus;
  const SlideAnalyticEntity({
    required this.id,
    this.dcrUuid,
    this.doctorUuid,
    required this.slideIndex,
    required this.durationSeconds,
    required this.timestamp,
    required this.syncStatus,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    if (!nullToAbsent || dcrUuid != null) {
      map['dcr_uuid'] = Variable<String>(dcrUuid);
    }
    if (!nullToAbsent || doctorUuid != null) {
      map['doctor_uuid'] = Variable<String>(doctorUuid);
    }
    map['slide_index'] = Variable<int>(slideIndex);
    map['duration_seconds'] = Variable<int>(durationSeconds);
    map['timestamp'] = Variable<DateTime>(timestamp);
    map['sync_status'] = Variable<String>(syncStatus);
    return map;
  }

  SlideAnalyticsCompanion toCompanion(bool nullToAbsent) {
    return SlideAnalyticsCompanion(
      id: Value(id),
      dcrUuid: dcrUuid == null && nullToAbsent
          ? const Value.absent()
          : Value(dcrUuid),
      doctorUuid: doctorUuid == null && nullToAbsent
          ? const Value.absent()
          : Value(doctorUuid),
      slideIndex: Value(slideIndex),
      durationSeconds: Value(durationSeconds),
      timestamp: Value(timestamp),
      syncStatus: Value(syncStatus),
    );
  }

  factory SlideAnalyticEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return SlideAnalyticEntity(
      id: serializer.fromJson<int>(json['id']),
      dcrUuid: serializer.fromJson<String?>(json['dcrUuid']),
      doctorUuid: serializer.fromJson<String?>(json['doctorUuid']),
      slideIndex: serializer.fromJson<int>(json['slideIndex']),
      durationSeconds: serializer.fromJson<int>(json['durationSeconds']),
      timestamp: serializer.fromJson<DateTime>(json['timestamp']),
      syncStatus: serializer.fromJson<String>(json['syncStatus']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'dcrUuid': serializer.toJson<String?>(dcrUuid),
      'doctorUuid': serializer.toJson<String?>(doctorUuid),
      'slideIndex': serializer.toJson<int>(slideIndex),
      'durationSeconds': serializer.toJson<int>(durationSeconds),
      'timestamp': serializer.toJson<DateTime>(timestamp),
      'syncStatus': serializer.toJson<String>(syncStatus),
    };
  }

  SlideAnalyticEntity copyWith({
    int? id,
    Value<String?> dcrUuid = const Value.absent(),
    Value<String?> doctorUuid = const Value.absent(),
    int? slideIndex,
    int? durationSeconds,
    DateTime? timestamp,
    String? syncStatus,
  }) => SlideAnalyticEntity(
    id: id ?? this.id,
    dcrUuid: dcrUuid.present ? dcrUuid.value : this.dcrUuid,
    doctorUuid: doctorUuid.present ? doctorUuid.value : this.doctorUuid,
    slideIndex: slideIndex ?? this.slideIndex,
    durationSeconds: durationSeconds ?? this.durationSeconds,
    timestamp: timestamp ?? this.timestamp,
    syncStatus: syncStatus ?? this.syncStatus,
  );
  SlideAnalyticEntity copyWithCompanion(SlideAnalyticsCompanion data) {
    return SlideAnalyticEntity(
      id: data.id.present ? data.id.value : this.id,
      dcrUuid: data.dcrUuid.present ? data.dcrUuid.value : this.dcrUuid,
      doctorUuid: data.doctorUuid.present
          ? data.doctorUuid.value
          : this.doctorUuid,
      slideIndex: data.slideIndex.present
          ? data.slideIndex.value
          : this.slideIndex,
      durationSeconds: data.durationSeconds.present
          ? data.durationSeconds.value
          : this.durationSeconds,
      timestamp: data.timestamp.present ? data.timestamp.value : this.timestamp,
      syncStatus: data.syncStatus.present
          ? data.syncStatus.value
          : this.syncStatus,
    );
  }

  @override
  String toString() {
    return (StringBuffer('SlideAnalyticEntity(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('doctorUuid: $doctorUuid, ')
          ..write('slideIndex: $slideIndex, ')
          ..write('durationSeconds: $durationSeconds, ')
          ..write('timestamp: $timestamp, ')
          ..write('syncStatus: $syncStatus')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    dcrUuid,
    doctorUuid,
    slideIndex,
    durationSeconds,
    timestamp,
    syncStatus,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is SlideAnalyticEntity &&
          other.id == this.id &&
          other.dcrUuid == this.dcrUuid &&
          other.doctorUuid == this.doctorUuid &&
          other.slideIndex == this.slideIndex &&
          other.durationSeconds == this.durationSeconds &&
          other.timestamp == this.timestamp &&
          other.syncStatus == this.syncStatus);
}

class SlideAnalyticsCompanion extends UpdateCompanion<SlideAnalyticEntity> {
  final Value<int> id;
  final Value<String?> dcrUuid;
  final Value<String?> doctorUuid;
  final Value<int> slideIndex;
  final Value<int> durationSeconds;
  final Value<DateTime> timestamp;
  final Value<String> syncStatus;
  const SlideAnalyticsCompanion({
    this.id = const Value.absent(),
    this.dcrUuid = const Value.absent(),
    this.doctorUuid = const Value.absent(),
    this.slideIndex = const Value.absent(),
    this.durationSeconds = const Value.absent(),
    this.timestamp = const Value.absent(),
    this.syncStatus = const Value.absent(),
  });
  SlideAnalyticsCompanion.insert({
    this.id = const Value.absent(),
    this.dcrUuid = const Value.absent(),
    this.doctorUuid = const Value.absent(),
    required int slideIndex,
    required int durationSeconds,
    required DateTime timestamp,
    this.syncStatus = const Value.absent(),
  }) : slideIndex = Value(slideIndex),
       durationSeconds = Value(durationSeconds),
       timestamp = Value(timestamp);
  static Insertable<SlideAnalyticEntity> custom({
    Expression<int>? id,
    Expression<String>? dcrUuid,
    Expression<String>? doctorUuid,
    Expression<int>? slideIndex,
    Expression<int>? durationSeconds,
    Expression<DateTime>? timestamp,
    Expression<String>? syncStatus,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dcrUuid != null) 'dcr_uuid': dcrUuid,
      if (doctorUuid != null) 'doctor_uuid': doctorUuid,
      if (slideIndex != null) 'slide_index': slideIndex,
      if (durationSeconds != null) 'duration_seconds': durationSeconds,
      if (timestamp != null) 'timestamp': timestamp,
      if (syncStatus != null) 'sync_status': syncStatus,
    });
  }

  SlideAnalyticsCompanion copyWith({
    Value<int>? id,
    Value<String?>? dcrUuid,
    Value<String?>? doctorUuid,
    Value<int>? slideIndex,
    Value<int>? durationSeconds,
    Value<DateTime>? timestamp,
    Value<String>? syncStatus,
  }) {
    return SlideAnalyticsCompanion(
      id: id ?? this.id,
      dcrUuid: dcrUuid ?? this.dcrUuid,
      doctorUuid: doctorUuid ?? this.doctorUuid,
      slideIndex: slideIndex ?? this.slideIndex,
      durationSeconds: durationSeconds ?? this.durationSeconds,
      timestamp: timestamp ?? this.timestamp,
      syncStatus: syncStatus ?? this.syncStatus,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (dcrUuid.present) {
      map['dcr_uuid'] = Variable<String>(dcrUuid.value);
    }
    if (doctorUuid.present) {
      map['doctor_uuid'] = Variable<String>(doctorUuid.value);
    }
    if (slideIndex.present) {
      map['slide_index'] = Variable<int>(slideIndex.value);
    }
    if (durationSeconds.present) {
      map['duration_seconds'] = Variable<int>(durationSeconds.value);
    }
    if (timestamp.present) {
      map['timestamp'] = Variable<DateTime>(timestamp.value);
    }
    if (syncStatus.present) {
      map['sync_status'] = Variable<String>(syncStatus.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('SlideAnalyticsCompanion(')
          ..write('id: $id, ')
          ..write('dcrUuid: $dcrUuid, ')
          ..write('doctorUuid: $doctorUuid, ')
          ..write('slideIndex: $slideIndex, ')
          ..write('durationSeconds: $durationSeconds, ')
          ..write('timestamp: $timestamp, ')
          ..write('syncStatus: $syncStatus')
          ..write(')'))
        .toString();
  }
}

class $SyncOutboxTable extends SyncOutbox
    with TableInfo<$SyncOutboxTable, SyncOutboxEntity> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $SyncOutboxTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    hasAutoIncrement: true,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultConstraints: GeneratedColumn.constraintIsAlways(
      'PRIMARY KEY AUTOINCREMENT',
    ),
  );
  static const VerificationMeta _entityTypeMeta = const VerificationMeta(
    'entityType',
  );
  @override
  late final GeneratedColumn<String> entityType = GeneratedColumn<String>(
    'entity_type',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _entityUuidMeta = const VerificationMeta(
    'entityUuid',
  );
  @override
  late final GeneratedColumn<String> entityUuid = GeneratedColumn<String>(
    'entity_uuid',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _payloadJsonMeta = const VerificationMeta(
    'payloadJson',
  );
  @override
  late final GeneratedColumn<String> payloadJson = GeneratedColumn<String>(
    'payload_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _statusMeta = const VerificationMeta('status');
  @override
  late final GeneratedColumn<String> status = GeneratedColumn<String>(
    'status',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('pending'),
  );
  static const VerificationMeta _retryCountMeta = const VerificationMeta(
    'retryCount',
  );
  @override
  late final GeneratedColumn<int> retryCount = GeneratedColumn<int>(
    'retry_count',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
    defaultValue: const Constant(0),
  );
  static const VerificationMeta _lastErrorMeta = const VerificationMeta(
    'lastError',
  );
  @override
  late final GeneratedColumn<String> lastError = GeneratedColumn<String>(
    'last_error',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _createdAtMeta = const VerificationMeta(
    'createdAt',
  );
  @override
  late final GeneratedColumn<DateTime> createdAt = GeneratedColumn<DateTime>(
    'created_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    entityType,
    entityUuid,
    payloadJson,
    status,
    retryCount,
    lastError,
    createdAt,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'sync_outbox';
  @override
  VerificationContext validateIntegrity(
    Insertable<SyncOutboxEntity> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('entity_type')) {
      context.handle(
        _entityTypeMeta,
        entityType.isAcceptableOrUnknown(data['entity_type']!, _entityTypeMeta),
      );
    } else if (isInserting) {
      context.missing(_entityTypeMeta);
    }
    if (data.containsKey('entity_uuid')) {
      context.handle(
        _entityUuidMeta,
        entityUuid.isAcceptableOrUnknown(data['entity_uuid']!, _entityUuidMeta),
      );
    } else if (isInserting) {
      context.missing(_entityUuidMeta);
    }
    if (data.containsKey('payload_json')) {
      context.handle(
        _payloadJsonMeta,
        payloadJson.isAcceptableOrUnknown(
          data['payload_json']!,
          _payloadJsonMeta,
        ),
      );
    } else if (isInserting) {
      context.missing(_payloadJsonMeta);
    }
    if (data.containsKey('status')) {
      context.handle(
        _statusMeta,
        status.isAcceptableOrUnknown(data['status']!, _statusMeta),
      );
    }
    if (data.containsKey('retry_count')) {
      context.handle(
        _retryCountMeta,
        retryCount.isAcceptableOrUnknown(data['retry_count']!, _retryCountMeta),
      );
    }
    if (data.containsKey('last_error')) {
      context.handle(
        _lastErrorMeta,
        lastError.isAcceptableOrUnknown(data['last_error']!, _lastErrorMeta),
      );
    }
    if (data.containsKey('created_at')) {
      context.handle(
        _createdAtMeta,
        createdAt.isAcceptableOrUnknown(data['created_at']!, _createdAtMeta),
      );
    } else if (isInserting) {
      context.missing(_createdAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  SyncOutboxEntity map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return SyncOutboxEntity(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      entityType: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}entity_type'],
      )!,
      entityUuid: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}entity_uuid'],
      )!,
      payloadJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}payload_json'],
      )!,
      status: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}status'],
      )!,
      retryCount: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}retry_count'],
      )!,
      lastError: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}last_error'],
      ),
      createdAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}created_at'],
      )!,
    );
  }

  @override
  $SyncOutboxTable createAlias(String alias) {
    return $SyncOutboxTable(attachedDatabase, alias);
  }
}

class SyncOutboxEntity extends DataClass
    implements Insertable<SyncOutboxEntity> {
  final int id;
  final String entityType;
  final String entityUuid;
  final String payloadJson;
  final String status;
  final int retryCount;
  final String? lastError;
  final DateTime createdAt;
  const SyncOutboxEntity({
    required this.id,
    required this.entityType,
    required this.entityUuid,
    required this.payloadJson,
    required this.status,
    required this.retryCount,
    this.lastError,
    required this.createdAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['entity_type'] = Variable<String>(entityType);
    map['entity_uuid'] = Variable<String>(entityUuid);
    map['payload_json'] = Variable<String>(payloadJson);
    map['status'] = Variable<String>(status);
    map['retry_count'] = Variable<int>(retryCount);
    if (!nullToAbsent || lastError != null) {
      map['last_error'] = Variable<String>(lastError);
    }
    map['created_at'] = Variable<DateTime>(createdAt);
    return map;
  }

  SyncOutboxCompanion toCompanion(bool nullToAbsent) {
    return SyncOutboxCompanion(
      id: Value(id),
      entityType: Value(entityType),
      entityUuid: Value(entityUuid),
      payloadJson: Value(payloadJson),
      status: Value(status),
      retryCount: Value(retryCount),
      lastError: lastError == null && nullToAbsent
          ? const Value.absent()
          : Value(lastError),
      createdAt: Value(createdAt),
    );
  }

  factory SyncOutboxEntity.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return SyncOutboxEntity(
      id: serializer.fromJson<int>(json['id']),
      entityType: serializer.fromJson<String>(json['entityType']),
      entityUuid: serializer.fromJson<String>(json['entityUuid']),
      payloadJson: serializer.fromJson<String>(json['payloadJson']),
      status: serializer.fromJson<String>(json['status']),
      retryCount: serializer.fromJson<int>(json['retryCount']),
      lastError: serializer.fromJson<String?>(json['lastError']),
      createdAt: serializer.fromJson<DateTime>(json['createdAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'entityType': serializer.toJson<String>(entityType),
      'entityUuid': serializer.toJson<String>(entityUuid),
      'payloadJson': serializer.toJson<String>(payloadJson),
      'status': serializer.toJson<String>(status),
      'retryCount': serializer.toJson<int>(retryCount),
      'lastError': serializer.toJson<String?>(lastError),
      'createdAt': serializer.toJson<DateTime>(createdAt),
    };
  }

  SyncOutboxEntity copyWith({
    int? id,
    String? entityType,
    String? entityUuid,
    String? payloadJson,
    String? status,
    int? retryCount,
    Value<String?> lastError = const Value.absent(),
    DateTime? createdAt,
  }) => SyncOutboxEntity(
    id: id ?? this.id,
    entityType: entityType ?? this.entityType,
    entityUuid: entityUuid ?? this.entityUuid,
    payloadJson: payloadJson ?? this.payloadJson,
    status: status ?? this.status,
    retryCount: retryCount ?? this.retryCount,
    lastError: lastError.present ? lastError.value : this.lastError,
    createdAt: createdAt ?? this.createdAt,
  );
  SyncOutboxEntity copyWithCompanion(SyncOutboxCompanion data) {
    return SyncOutboxEntity(
      id: data.id.present ? data.id.value : this.id,
      entityType: data.entityType.present
          ? data.entityType.value
          : this.entityType,
      entityUuid: data.entityUuid.present
          ? data.entityUuid.value
          : this.entityUuid,
      payloadJson: data.payloadJson.present
          ? data.payloadJson.value
          : this.payloadJson,
      status: data.status.present ? data.status.value : this.status,
      retryCount: data.retryCount.present
          ? data.retryCount.value
          : this.retryCount,
      lastError: data.lastError.present ? data.lastError.value : this.lastError,
      createdAt: data.createdAt.present ? data.createdAt.value : this.createdAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('SyncOutboxEntity(')
          ..write('id: $id, ')
          ..write('entityType: $entityType, ')
          ..write('entityUuid: $entityUuid, ')
          ..write('payloadJson: $payloadJson, ')
          ..write('status: $status, ')
          ..write('retryCount: $retryCount, ')
          ..write('lastError: $lastError, ')
          ..write('createdAt: $createdAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    entityType,
    entityUuid,
    payloadJson,
    status,
    retryCount,
    lastError,
    createdAt,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is SyncOutboxEntity &&
          other.id == this.id &&
          other.entityType == this.entityType &&
          other.entityUuid == this.entityUuid &&
          other.payloadJson == this.payloadJson &&
          other.status == this.status &&
          other.retryCount == this.retryCount &&
          other.lastError == this.lastError &&
          other.createdAt == this.createdAt);
}

class SyncOutboxCompanion extends UpdateCompanion<SyncOutboxEntity> {
  final Value<int> id;
  final Value<String> entityType;
  final Value<String> entityUuid;
  final Value<String> payloadJson;
  final Value<String> status;
  final Value<int> retryCount;
  final Value<String?> lastError;
  final Value<DateTime> createdAt;
  const SyncOutboxCompanion({
    this.id = const Value.absent(),
    this.entityType = const Value.absent(),
    this.entityUuid = const Value.absent(),
    this.payloadJson = const Value.absent(),
    this.status = const Value.absent(),
    this.retryCount = const Value.absent(),
    this.lastError = const Value.absent(),
    this.createdAt = const Value.absent(),
  });
  SyncOutboxCompanion.insert({
    this.id = const Value.absent(),
    required String entityType,
    required String entityUuid,
    required String payloadJson,
    this.status = const Value.absent(),
    this.retryCount = const Value.absent(),
    this.lastError = const Value.absent(),
    required DateTime createdAt,
  }) : entityType = Value(entityType),
       entityUuid = Value(entityUuid),
       payloadJson = Value(payloadJson),
       createdAt = Value(createdAt);
  static Insertable<SyncOutboxEntity> custom({
    Expression<int>? id,
    Expression<String>? entityType,
    Expression<String>? entityUuid,
    Expression<String>? payloadJson,
    Expression<String>? status,
    Expression<int>? retryCount,
    Expression<String>? lastError,
    Expression<DateTime>? createdAt,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (entityType != null) 'entity_type': entityType,
      if (entityUuid != null) 'entity_uuid': entityUuid,
      if (payloadJson != null) 'payload_json': payloadJson,
      if (status != null) 'status': status,
      if (retryCount != null) 'retry_count': retryCount,
      if (lastError != null) 'last_error': lastError,
      if (createdAt != null) 'created_at': createdAt,
    });
  }

  SyncOutboxCompanion copyWith({
    Value<int>? id,
    Value<String>? entityType,
    Value<String>? entityUuid,
    Value<String>? payloadJson,
    Value<String>? status,
    Value<int>? retryCount,
    Value<String?>? lastError,
    Value<DateTime>? createdAt,
  }) {
    return SyncOutboxCompanion(
      id: id ?? this.id,
      entityType: entityType ?? this.entityType,
      entityUuid: entityUuid ?? this.entityUuid,
      payloadJson: payloadJson ?? this.payloadJson,
      status: status ?? this.status,
      retryCount: retryCount ?? this.retryCount,
      lastError: lastError ?? this.lastError,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (entityType.present) {
      map['entity_type'] = Variable<String>(entityType.value);
    }
    if (entityUuid.present) {
      map['entity_uuid'] = Variable<String>(entityUuid.value);
    }
    if (payloadJson.present) {
      map['payload_json'] = Variable<String>(payloadJson.value);
    }
    if (status.present) {
      map['status'] = Variable<String>(status.value);
    }
    if (retryCount.present) {
      map['retry_count'] = Variable<int>(retryCount.value);
    }
    if (lastError.present) {
      map['last_error'] = Variable<String>(lastError.value);
    }
    if (createdAt.present) {
      map['created_at'] = Variable<DateTime>(createdAt.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('SyncOutboxCompanion(')
          ..write('id: $id, ')
          ..write('entityType: $entityType, ')
          ..write('entityUuid: $entityUuid, ')
          ..write('payloadJson: $payloadJson, ')
          ..write('status: $status, ')
          ..write('retryCount: $retryCount, ')
          ..write('lastError: $lastError, ')
          ..write('createdAt: $createdAt')
          ..write(')'))
        .toString();
  }
}

abstract class _$AppDatabase extends GeneratedDatabase {
  _$AppDatabase(QueryExecutor e) : super(e);
  $AppDatabaseManager get managers => $AppDatabaseManager(this);
  late final $DoctorsTable doctors = $DoctorsTable(this);
  late final $ProductsTable products = $ProductsTable(this);
  late final $PromotionalInputsTable promotionalInputs =
      $PromotionalInputsTable(this);
  late final $DcrsTable dcrs = $DcrsTable(this);
  late final $DcrProductsTable dcrProducts = $DcrProductsTable(this);
  late final $DcrInputsTable dcrInputs = $DcrInputsTable(this);
  late final $SlideAnalyticsTable slideAnalytics = $SlideAnalyticsTable(this);
  late final $SyncOutboxTable syncOutbox = $SyncOutboxTable(this);
  @override
  Iterable<TableInfo<Table, Object?>> get allTables =>
      allSchemaEntities.whereType<TableInfo<Table, Object?>>();
  @override
  List<DatabaseSchemaEntity> get allSchemaEntities => [
    doctors,
    products,
    promotionalInputs,
    dcrs,
    dcrProducts,
    dcrInputs,
    slideAnalytics,
    syncOutbox,
  ];
}

typedef $$DoctorsTableCreateCompanionBuilder =
    DoctorsCompanion Function({
      Value<int?> id,
      required String uuid,
      required String name,
      Value<String?> email,
      Value<String?> phone,
      Value<String?> specialty,
      Value<String?> qualification,
      Value<String?> town,
      Value<int?> areaId,
      Value<String?> clinicName,
      Value<String?> address,
      Value<double?> latitude,
      Value<double?> longitude,
      Value<String> status,
      Value<String> syncStatus,
      Value<DateTime?> updatedAt,
      Value<int> rowid,
    });
typedef $$DoctorsTableUpdateCompanionBuilder =
    DoctorsCompanion Function({
      Value<int?> id,
      Value<String> uuid,
      Value<String> name,
      Value<String?> email,
      Value<String?> phone,
      Value<String?> specialty,
      Value<String?> qualification,
      Value<String?> town,
      Value<int?> areaId,
      Value<String?> clinicName,
      Value<String?> address,
      Value<double?> latitude,
      Value<double?> longitude,
      Value<String> status,
      Value<String> syncStatus,
      Value<DateTime?> updatedAt,
      Value<int> rowid,
    });

class $$DoctorsTableFilterComposer
    extends Composer<_$AppDatabase, $DoctorsTable> {
  $$DoctorsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get uuid => $composableBuilder(
    column: $table.uuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get email => $composableBuilder(
    column: $table.email,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get phone => $composableBuilder(
    column: $table.phone,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get specialty => $composableBuilder(
    column: $table.specialty,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get qualification => $composableBuilder(
    column: $table.qualification,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get town => $composableBuilder(
    column: $table.town,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get areaId => $composableBuilder(
    column: $table.areaId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get clinicName => $composableBuilder(
    column: $table.clinicName,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get address => $composableBuilder(
    column: $table.address,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get latitude => $composableBuilder(
    column: $table.latitude,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get longitude => $composableBuilder(
    column: $table.longitude,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get status => $composableBuilder(
    column: $table.status,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$DoctorsTableOrderingComposer
    extends Composer<_$AppDatabase, $DoctorsTable> {
  $$DoctorsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get uuid => $composableBuilder(
    column: $table.uuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get email => $composableBuilder(
    column: $table.email,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get phone => $composableBuilder(
    column: $table.phone,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get specialty => $composableBuilder(
    column: $table.specialty,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get qualification => $composableBuilder(
    column: $table.qualification,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get town => $composableBuilder(
    column: $table.town,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get areaId => $composableBuilder(
    column: $table.areaId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get clinicName => $composableBuilder(
    column: $table.clinicName,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get address => $composableBuilder(
    column: $table.address,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get latitude => $composableBuilder(
    column: $table.latitude,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get longitude => $composableBuilder(
    column: $table.longitude,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get status => $composableBuilder(
    column: $table.status,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$DoctorsTableAnnotationComposer
    extends Composer<_$AppDatabase, $DoctorsTable> {
  $$DoctorsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get uuid =>
      $composableBuilder(column: $table.uuid, builder: (column) => column);

  GeneratedColumn<String> get name =>
      $composableBuilder(column: $table.name, builder: (column) => column);

  GeneratedColumn<String> get email =>
      $composableBuilder(column: $table.email, builder: (column) => column);

  GeneratedColumn<String> get phone =>
      $composableBuilder(column: $table.phone, builder: (column) => column);

  GeneratedColumn<String> get specialty =>
      $composableBuilder(column: $table.specialty, builder: (column) => column);

  GeneratedColumn<String> get qualification => $composableBuilder(
    column: $table.qualification,
    builder: (column) => column,
  );

  GeneratedColumn<String> get town =>
      $composableBuilder(column: $table.town, builder: (column) => column);

  GeneratedColumn<int> get areaId =>
      $composableBuilder(column: $table.areaId, builder: (column) => column);

  GeneratedColumn<String> get clinicName => $composableBuilder(
    column: $table.clinicName,
    builder: (column) => column,
  );

  GeneratedColumn<String> get address =>
      $composableBuilder(column: $table.address, builder: (column) => column);

  GeneratedColumn<double> get latitude =>
      $composableBuilder(column: $table.latitude, builder: (column) => column);

  GeneratedColumn<double> get longitude =>
      $composableBuilder(column: $table.longitude, builder: (column) => column);

  GeneratedColumn<String> get status =>
      $composableBuilder(column: $table.status, builder: (column) => column);

  GeneratedColumn<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$DoctorsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $DoctorsTable,
          DoctorEntity,
          $$DoctorsTableFilterComposer,
          $$DoctorsTableOrderingComposer,
          $$DoctorsTableAnnotationComposer,
          $$DoctorsTableCreateCompanionBuilder,
          $$DoctorsTableUpdateCompanionBuilder,
          (
            DoctorEntity,
            BaseReferences<_$AppDatabase, $DoctorsTable, DoctorEntity>,
          ),
          DoctorEntity,
          PrefetchHooks Function()
        > {
  $$DoctorsTableTableManager(_$AppDatabase db, $DoctorsTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$DoctorsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$DoctorsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$DoctorsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int?> id = const Value.absent(),
                Value<String> uuid = const Value.absent(),
                Value<String> name = const Value.absent(),
                Value<String?> email = const Value.absent(),
                Value<String?> phone = const Value.absent(),
                Value<String?> specialty = const Value.absent(),
                Value<String?> qualification = const Value.absent(),
                Value<String?> town = const Value.absent(),
                Value<int?> areaId = const Value.absent(),
                Value<String?> clinicName = const Value.absent(),
                Value<String?> address = const Value.absent(),
                Value<double?> latitude = const Value.absent(),
                Value<double?> longitude = const Value.absent(),
                Value<String> status = const Value.absent(),
                Value<String> syncStatus = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => DoctorsCompanion(
                id: id,
                uuid: uuid,
                name: name,
                email: email,
                phone: phone,
                specialty: specialty,
                qualification: qualification,
                town: town,
                areaId: areaId,
                clinicName: clinicName,
                address: address,
                latitude: latitude,
                longitude: longitude,
                status: status,
                syncStatus: syncStatus,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                Value<int?> id = const Value.absent(),
                required String uuid,
                required String name,
                Value<String?> email = const Value.absent(),
                Value<String?> phone = const Value.absent(),
                Value<String?> specialty = const Value.absent(),
                Value<String?> qualification = const Value.absent(),
                Value<String?> town = const Value.absent(),
                Value<int?> areaId = const Value.absent(),
                Value<String?> clinicName = const Value.absent(),
                Value<String?> address = const Value.absent(),
                Value<double?> latitude = const Value.absent(),
                Value<double?> longitude = const Value.absent(),
                Value<String> status = const Value.absent(),
                Value<String> syncStatus = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => DoctorsCompanion.insert(
                id: id,
                uuid: uuid,
                name: name,
                email: email,
                phone: phone,
                specialty: specialty,
                qualification: qualification,
                town: town,
                areaId: areaId,
                clinicName: clinicName,
                address: address,
                latitude: latitude,
                longitude: longitude,
                status: status,
                syncStatus: syncStatus,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$DoctorsTable, DoctorEntity>(table),
                  BaseReferences<_$AppDatabase, $DoctorsTable, DoctorEntity>(
                    db,
                    table,
                    e,
                  ),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$DoctorsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $DoctorsTable,
      DoctorEntity,
      $$DoctorsTableFilterComposer,
      $$DoctorsTableOrderingComposer,
      $$DoctorsTableAnnotationComposer,
      $$DoctorsTableCreateCompanionBuilder,
      $$DoctorsTableUpdateCompanionBuilder,
      (
        DoctorEntity,
        BaseReferences<_$AppDatabase, $DoctorsTable, DoctorEntity>,
      ),
      DoctorEntity,
      PrefetchHooks Function()
    >;
typedef $$ProductsTableCreateCompanionBuilder =
    ProductsCompanion Function({
      Value<int> id,
      required String name,
      Value<int?> therapeuticAreaId,
      Value<DateTime?> updatedAt,
    });
typedef $$ProductsTableUpdateCompanionBuilder =
    ProductsCompanion Function({
      Value<int> id,
      Value<String> name,
      Value<int?> therapeuticAreaId,
      Value<DateTime?> updatedAt,
    });

class $$ProductsTableFilterComposer
    extends Composer<_$AppDatabase, $ProductsTable> {
  $$ProductsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get therapeuticAreaId => $composableBuilder(
    column: $table.therapeuticAreaId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$ProductsTableOrderingComposer
    extends Composer<_$AppDatabase, $ProductsTable> {
  $$ProductsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get therapeuticAreaId => $composableBuilder(
    column: $table.therapeuticAreaId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$ProductsTableAnnotationComposer
    extends Composer<_$AppDatabase, $ProductsTable> {
  $$ProductsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get name =>
      $composableBuilder(column: $table.name, builder: (column) => column);

  GeneratedColumn<int> get therapeuticAreaId => $composableBuilder(
    column: $table.therapeuticAreaId,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$ProductsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $ProductsTable,
          ProductEntity,
          $$ProductsTableFilterComposer,
          $$ProductsTableOrderingComposer,
          $$ProductsTableAnnotationComposer,
          $$ProductsTableCreateCompanionBuilder,
          $$ProductsTableUpdateCompanionBuilder,
          (
            ProductEntity,
            BaseReferences<_$AppDatabase, $ProductsTable, ProductEntity>,
          ),
          ProductEntity,
          PrefetchHooks Function()
        > {
  $$ProductsTableTableManager(_$AppDatabase db, $ProductsTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$ProductsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$ProductsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$ProductsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> name = const Value.absent(),
                Value<int?> therapeuticAreaId = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
              }) => ProductsCompanion(
                id: id,
                name: name,
                therapeuticAreaId: therapeuticAreaId,
                updatedAt: updatedAt,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String name,
                Value<int?> therapeuticAreaId = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
              }) => ProductsCompanion.insert(
                id: id,
                name: name,
                therapeuticAreaId: therapeuticAreaId,
                updatedAt: updatedAt,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$ProductsTable, ProductEntity>(table),
                  BaseReferences<_$AppDatabase, $ProductsTable, ProductEntity>(
                    db,
                    table,
                    e,
                  ),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$ProductsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $ProductsTable,
      ProductEntity,
      $$ProductsTableFilterComposer,
      $$ProductsTableOrderingComposer,
      $$ProductsTableAnnotationComposer,
      $$ProductsTableCreateCompanionBuilder,
      $$ProductsTableUpdateCompanionBuilder,
      (
        ProductEntity,
        BaseReferences<_$AppDatabase, $ProductsTable, ProductEntity>,
      ),
      ProductEntity,
      PrefetchHooks Function()
    >;
typedef $$PromotionalInputsTableCreateCompanionBuilder =
    PromotionalInputsCompanion Function({
      Value<int> id,
      required String name,
      Value<String?> type,
      Value<DateTime?> updatedAt,
    });
typedef $$PromotionalInputsTableUpdateCompanionBuilder =
    PromotionalInputsCompanion Function({
      Value<int> id,
      Value<String> name,
      Value<String?> type,
      Value<DateTime?> updatedAt,
    });

class $$PromotionalInputsTableFilterComposer
    extends Composer<_$AppDatabase, $PromotionalInputsTable> {
  $$PromotionalInputsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get type => $composableBuilder(
    column: $table.type,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$PromotionalInputsTableOrderingComposer
    extends Composer<_$AppDatabase, $PromotionalInputsTable> {
  $$PromotionalInputsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get name => $composableBuilder(
    column: $table.name,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get type => $composableBuilder(
    column: $table.type,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$PromotionalInputsTableAnnotationComposer
    extends Composer<_$AppDatabase, $PromotionalInputsTable> {
  $$PromotionalInputsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get name =>
      $composableBuilder(column: $table.name, builder: (column) => column);

  GeneratedColumn<String> get type =>
      $composableBuilder(column: $table.type, builder: (column) => column);

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$PromotionalInputsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $PromotionalInputsTable,
          PromotionalInputEntity,
          $$PromotionalInputsTableFilterComposer,
          $$PromotionalInputsTableOrderingComposer,
          $$PromotionalInputsTableAnnotationComposer,
          $$PromotionalInputsTableCreateCompanionBuilder,
          $$PromotionalInputsTableUpdateCompanionBuilder,
          (
            PromotionalInputEntity,
            BaseReferences<
              _$AppDatabase,
              $PromotionalInputsTable,
              PromotionalInputEntity
            >,
          ),
          PromotionalInputEntity,
          PrefetchHooks Function()
        > {
  $$PromotionalInputsTableTableManager(
    _$AppDatabase db,
    $PromotionalInputsTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$PromotionalInputsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$PromotionalInputsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$PromotionalInputsTableAnnotationComposer(
                $db: db,
                $table: table,
              ),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> name = const Value.absent(),
                Value<String?> type = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
              }) => PromotionalInputsCompanion(
                id: id,
                name: name,
                type: type,
                updatedAt: updatedAt,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String name,
                Value<String?> type = const Value.absent(),
                Value<DateTime?> updatedAt = const Value.absent(),
              }) => PromotionalInputsCompanion.insert(
                id: id,
                name: name,
                type: type,
                updatedAt: updatedAt,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$PromotionalInputsTable, PromotionalInputEntity>(
                    table,
                  ),
                  BaseReferences<
                    _$AppDatabase,
                    $PromotionalInputsTable,
                    PromotionalInputEntity
                  >(db, table, e),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$PromotionalInputsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $PromotionalInputsTable,
      PromotionalInputEntity,
      $$PromotionalInputsTableFilterComposer,
      $$PromotionalInputsTableOrderingComposer,
      $$PromotionalInputsTableAnnotationComposer,
      $$PromotionalInputsTableCreateCompanionBuilder,
      $$PromotionalInputsTableUpdateCompanionBuilder,
      (
        PromotionalInputEntity,
        BaseReferences<
          _$AppDatabase,
          $PromotionalInputsTable,
          PromotionalInputEntity
        >,
      ),
      PromotionalInputEntity,
      PrefetchHooks Function()
    >;
typedef $$DcrsTableCreateCompanionBuilder =
    DcrsCompanion Function({
      Value<int?> id,
      required String uuid,
      required DateTime date,
      required String doctorUuid,
      Value<int?> doctorId,
      Value<String?> doctorName,
      Value<String?> remarks,
      Value<double?> latitude,
      Value<double?> longitude,
      Value<String?> signatureSvg,
      Value<String> syncStatus,
      required DateTime createdAt,
      Value<int> rowid,
    });
typedef $$DcrsTableUpdateCompanionBuilder =
    DcrsCompanion Function({
      Value<int?> id,
      Value<String> uuid,
      Value<DateTime> date,
      Value<String> doctorUuid,
      Value<int?> doctorId,
      Value<String?> doctorName,
      Value<String?> remarks,
      Value<double?> latitude,
      Value<double?> longitude,
      Value<String?> signatureSvg,
      Value<String> syncStatus,
      Value<DateTime> createdAt,
      Value<int> rowid,
    });

class $$DcrsTableFilterComposer extends Composer<_$AppDatabase, $DcrsTable> {
  $$DcrsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get uuid => $composableBuilder(
    column: $table.uuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get date => $composableBuilder(
    column: $table.date,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get doctorId => $composableBuilder(
    column: $table.doctorId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get doctorName => $composableBuilder(
    column: $table.doctorName,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get remarks => $composableBuilder(
    column: $table.remarks,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get latitude => $composableBuilder(
    column: $table.latitude,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get longitude => $composableBuilder(
    column: $table.longitude,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get signatureSvg => $composableBuilder(
    column: $table.signatureSvg,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get createdAt => $composableBuilder(
    column: $table.createdAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$DcrsTableOrderingComposer extends Composer<_$AppDatabase, $DcrsTable> {
  $$DcrsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get uuid => $composableBuilder(
    column: $table.uuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get date => $composableBuilder(
    column: $table.date,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get doctorId => $composableBuilder(
    column: $table.doctorId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get doctorName => $composableBuilder(
    column: $table.doctorName,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get remarks => $composableBuilder(
    column: $table.remarks,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get latitude => $composableBuilder(
    column: $table.latitude,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get longitude => $composableBuilder(
    column: $table.longitude,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get signatureSvg => $composableBuilder(
    column: $table.signatureSvg,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get createdAt => $composableBuilder(
    column: $table.createdAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$DcrsTableAnnotationComposer
    extends Composer<_$AppDatabase, $DcrsTable> {
  $$DcrsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get uuid =>
      $composableBuilder(column: $table.uuid, builder: (column) => column);

  GeneratedColumn<DateTime> get date =>
      $composableBuilder(column: $table.date, builder: (column) => column);

  GeneratedColumn<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => column,
  );

  GeneratedColumn<int> get doctorId =>
      $composableBuilder(column: $table.doctorId, builder: (column) => column);

  GeneratedColumn<String> get doctorName => $composableBuilder(
    column: $table.doctorName,
    builder: (column) => column,
  );

  GeneratedColumn<String> get remarks =>
      $composableBuilder(column: $table.remarks, builder: (column) => column);

  GeneratedColumn<double> get latitude =>
      $composableBuilder(column: $table.latitude, builder: (column) => column);

  GeneratedColumn<double> get longitude =>
      $composableBuilder(column: $table.longitude, builder: (column) => column);

  GeneratedColumn<String> get signatureSvg => $composableBuilder(
    column: $table.signatureSvg,
    builder: (column) => column,
  );

  GeneratedColumn<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get createdAt =>
      $composableBuilder(column: $table.createdAt, builder: (column) => column);
}

class $$DcrsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $DcrsTable,
          DcrEntity,
          $$DcrsTableFilterComposer,
          $$DcrsTableOrderingComposer,
          $$DcrsTableAnnotationComposer,
          $$DcrsTableCreateCompanionBuilder,
          $$DcrsTableUpdateCompanionBuilder,
          (DcrEntity, BaseReferences<_$AppDatabase, $DcrsTable, DcrEntity>),
          DcrEntity,
          PrefetchHooks Function()
        > {
  $$DcrsTableTableManager(_$AppDatabase db, $DcrsTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$DcrsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$DcrsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$DcrsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int?> id = const Value.absent(),
                Value<String> uuid = const Value.absent(),
                Value<DateTime> date = const Value.absent(),
                Value<String> doctorUuid = const Value.absent(),
                Value<int?> doctorId = const Value.absent(),
                Value<String?> doctorName = const Value.absent(),
                Value<String?> remarks = const Value.absent(),
                Value<double?> latitude = const Value.absent(),
                Value<double?> longitude = const Value.absent(),
                Value<String?> signatureSvg = const Value.absent(),
                Value<String> syncStatus = const Value.absent(),
                Value<DateTime> createdAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => DcrsCompanion(
                id: id,
                uuid: uuid,
                date: date,
                doctorUuid: doctorUuid,
                doctorId: doctorId,
                doctorName: doctorName,
                remarks: remarks,
                latitude: latitude,
                longitude: longitude,
                signatureSvg: signatureSvg,
                syncStatus: syncStatus,
                createdAt: createdAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                Value<int?> id = const Value.absent(),
                required String uuid,
                required DateTime date,
                required String doctorUuid,
                Value<int?> doctorId = const Value.absent(),
                Value<String?> doctorName = const Value.absent(),
                Value<String?> remarks = const Value.absent(),
                Value<double?> latitude = const Value.absent(),
                Value<double?> longitude = const Value.absent(),
                Value<String?> signatureSvg = const Value.absent(),
                Value<String> syncStatus = const Value.absent(),
                required DateTime createdAt,
                Value<int> rowid = const Value.absent(),
              }) => DcrsCompanion.insert(
                id: id,
                uuid: uuid,
                date: date,
                doctorUuid: doctorUuid,
                doctorId: doctorId,
                doctorName: doctorName,
                remarks: remarks,
                latitude: latitude,
                longitude: longitude,
                signatureSvg: signatureSvg,
                syncStatus: syncStatus,
                createdAt: createdAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$DcrsTable, DcrEntity>(table),
                  BaseReferences<_$AppDatabase, $DcrsTable, DcrEntity>(
                    db,
                    table,
                    e,
                  ),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$DcrsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $DcrsTable,
      DcrEntity,
      $$DcrsTableFilterComposer,
      $$DcrsTableOrderingComposer,
      $$DcrsTableAnnotationComposer,
      $$DcrsTableCreateCompanionBuilder,
      $$DcrsTableUpdateCompanionBuilder,
      (DcrEntity, BaseReferences<_$AppDatabase, $DcrsTable, DcrEntity>),
      DcrEntity,
      PrefetchHooks Function()
    >;
typedef $$DcrProductsTableCreateCompanionBuilder =
    DcrProductsCompanion Function({
      Value<int> id,
      required String dcrUuid,
      required int productId,
      Value<int> quantity,
    });
typedef $$DcrProductsTableUpdateCompanionBuilder =
    DcrProductsCompanion Function({
      Value<int> id,
      Value<String> dcrUuid,
      Value<int> productId,
      Value<int> quantity,
    });

class $$DcrProductsTableFilterComposer
    extends Composer<_$AppDatabase, $DcrProductsTable> {
  $$DcrProductsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get productId => $composableBuilder(
    column: $table.productId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get quantity => $composableBuilder(
    column: $table.quantity,
    builder: (column) => ColumnFilters(column),
  );
}

class $$DcrProductsTableOrderingComposer
    extends Composer<_$AppDatabase, $DcrProductsTable> {
  $$DcrProductsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get productId => $composableBuilder(
    column: $table.productId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get quantity => $composableBuilder(
    column: $table.quantity,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$DcrProductsTableAnnotationComposer
    extends Composer<_$AppDatabase, $DcrProductsTable> {
  $$DcrProductsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dcrUuid =>
      $composableBuilder(column: $table.dcrUuid, builder: (column) => column);

  GeneratedColumn<int> get productId =>
      $composableBuilder(column: $table.productId, builder: (column) => column);

  GeneratedColumn<int> get quantity =>
      $composableBuilder(column: $table.quantity, builder: (column) => column);
}

class $$DcrProductsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $DcrProductsTable,
          DcrProductEntity,
          $$DcrProductsTableFilterComposer,
          $$DcrProductsTableOrderingComposer,
          $$DcrProductsTableAnnotationComposer,
          $$DcrProductsTableCreateCompanionBuilder,
          $$DcrProductsTableUpdateCompanionBuilder,
          (
            DcrProductEntity,
            BaseReferences<_$AppDatabase, $DcrProductsTable, DcrProductEntity>,
          ),
          DcrProductEntity,
          PrefetchHooks Function()
        > {
  $$DcrProductsTableTableManager(_$AppDatabase db, $DcrProductsTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$DcrProductsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$DcrProductsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$DcrProductsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> dcrUuid = const Value.absent(),
                Value<int> productId = const Value.absent(),
                Value<int> quantity = const Value.absent(),
              }) => DcrProductsCompanion(
                id: id,
                dcrUuid: dcrUuid,
                productId: productId,
                quantity: quantity,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String dcrUuid,
                required int productId,
                Value<int> quantity = const Value.absent(),
              }) => DcrProductsCompanion.insert(
                id: id,
                dcrUuid: dcrUuid,
                productId: productId,
                quantity: quantity,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$DcrProductsTable, DcrProductEntity>(table),
                  BaseReferences<
                    _$AppDatabase,
                    $DcrProductsTable,
                    DcrProductEntity
                  >(db, table, e),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$DcrProductsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $DcrProductsTable,
      DcrProductEntity,
      $$DcrProductsTableFilterComposer,
      $$DcrProductsTableOrderingComposer,
      $$DcrProductsTableAnnotationComposer,
      $$DcrProductsTableCreateCompanionBuilder,
      $$DcrProductsTableUpdateCompanionBuilder,
      (
        DcrProductEntity,
        BaseReferences<_$AppDatabase, $DcrProductsTable, DcrProductEntity>,
      ),
      DcrProductEntity,
      PrefetchHooks Function()
    >;
typedef $$DcrInputsTableCreateCompanionBuilder =
    DcrInputsCompanion Function({
      Value<int> id,
      required String dcrUuid,
      required int promotionalInputId,
      Value<int> quantity,
    });
typedef $$DcrInputsTableUpdateCompanionBuilder =
    DcrInputsCompanion Function({
      Value<int> id,
      Value<String> dcrUuid,
      Value<int> promotionalInputId,
      Value<int> quantity,
    });

class $$DcrInputsTableFilterComposer
    extends Composer<_$AppDatabase, $DcrInputsTable> {
  $$DcrInputsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get promotionalInputId => $composableBuilder(
    column: $table.promotionalInputId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get quantity => $composableBuilder(
    column: $table.quantity,
    builder: (column) => ColumnFilters(column),
  );
}

class $$DcrInputsTableOrderingComposer
    extends Composer<_$AppDatabase, $DcrInputsTable> {
  $$DcrInputsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get promotionalInputId => $composableBuilder(
    column: $table.promotionalInputId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get quantity => $composableBuilder(
    column: $table.quantity,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$DcrInputsTableAnnotationComposer
    extends Composer<_$AppDatabase, $DcrInputsTable> {
  $$DcrInputsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dcrUuid =>
      $composableBuilder(column: $table.dcrUuid, builder: (column) => column);

  GeneratedColumn<int> get promotionalInputId => $composableBuilder(
    column: $table.promotionalInputId,
    builder: (column) => column,
  );

  GeneratedColumn<int> get quantity =>
      $composableBuilder(column: $table.quantity, builder: (column) => column);
}

class $$DcrInputsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $DcrInputsTable,
          DcrInputEntity,
          $$DcrInputsTableFilterComposer,
          $$DcrInputsTableOrderingComposer,
          $$DcrInputsTableAnnotationComposer,
          $$DcrInputsTableCreateCompanionBuilder,
          $$DcrInputsTableUpdateCompanionBuilder,
          (
            DcrInputEntity,
            BaseReferences<_$AppDatabase, $DcrInputsTable, DcrInputEntity>,
          ),
          DcrInputEntity,
          PrefetchHooks Function()
        > {
  $$DcrInputsTableTableManager(_$AppDatabase db, $DcrInputsTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$DcrInputsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$DcrInputsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$DcrInputsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> dcrUuid = const Value.absent(),
                Value<int> promotionalInputId = const Value.absent(),
                Value<int> quantity = const Value.absent(),
              }) => DcrInputsCompanion(
                id: id,
                dcrUuid: dcrUuid,
                promotionalInputId: promotionalInputId,
                quantity: quantity,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String dcrUuid,
                required int promotionalInputId,
                Value<int> quantity = const Value.absent(),
              }) => DcrInputsCompanion.insert(
                id: id,
                dcrUuid: dcrUuid,
                promotionalInputId: promotionalInputId,
                quantity: quantity,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$DcrInputsTable, DcrInputEntity>(table),
                  BaseReferences<
                    _$AppDatabase,
                    $DcrInputsTable,
                    DcrInputEntity
                  >(db, table, e),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$DcrInputsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $DcrInputsTable,
      DcrInputEntity,
      $$DcrInputsTableFilterComposer,
      $$DcrInputsTableOrderingComposer,
      $$DcrInputsTableAnnotationComposer,
      $$DcrInputsTableCreateCompanionBuilder,
      $$DcrInputsTableUpdateCompanionBuilder,
      (
        DcrInputEntity,
        BaseReferences<_$AppDatabase, $DcrInputsTable, DcrInputEntity>,
      ),
      DcrInputEntity,
      PrefetchHooks Function()
    >;
typedef $$SlideAnalyticsTableCreateCompanionBuilder =
    SlideAnalyticsCompanion Function({
      Value<int> id,
      Value<String?> dcrUuid,
      Value<String?> doctorUuid,
      required int slideIndex,
      required int durationSeconds,
      required DateTime timestamp,
      Value<String> syncStatus,
    });
typedef $$SlideAnalyticsTableUpdateCompanionBuilder =
    SlideAnalyticsCompanion Function({
      Value<int> id,
      Value<String?> dcrUuid,
      Value<String?> doctorUuid,
      Value<int> slideIndex,
      Value<int> durationSeconds,
      Value<DateTime> timestamp,
      Value<String> syncStatus,
    });

class $$SlideAnalyticsTableFilterComposer
    extends Composer<_$AppDatabase, $SlideAnalyticsTable> {
  $$SlideAnalyticsTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get slideIndex => $composableBuilder(
    column: $table.slideIndex,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get durationSeconds => $composableBuilder(
    column: $table.durationSeconds,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get timestamp => $composableBuilder(
    column: $table.timestamp,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnFilters(column),
  );
}

class $$SlideAnalyticsTableOrderingComposer
    extends Composer<_$AppDatabase, $SlideAnalyticsTable> {
  $$SlideAnalyticsTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dcrUuid => $composableBuilder(
    column: $table.dcrUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get slideIndex => $composableBuilder(
    column: $table.slideIndex,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get durationSeconds => $composableBuilder(
    column: $table.durationSeconds,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get timestamp => $composableBuilder(
    column: $table.timestamp,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$SlideAnalyticsTableAnnotationComposer
    extends Composer<_$AppDatabase, $SlideAnalyticsTable> {
  $$SlideAnalyticsTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dcrUuid =>
      $composableBuilder(column: $table.dcrUuid, builder: (column) => column);

  GeneratedColumn<String> get doctorUuid => $composableBuilder(
    column: $table.doctorUuid,
    builder: (column) => column,
  );

  GeneratedColumn<int> get slideIndex => $composableBuilder(
    column: $table.slideIndex,
    builder: (column) => column,
  );

  GeneratedColumn<int> get durationSeconds => $composableBuilder(
    column: $table.durationSeconds,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get timestamp =>
      $composableBuilder(column: $table.timestamp, builder: (column) => column);

  GeneratedColumn<String> get syncStatus => $composableBuilder(
    column: $table.syncStatus,
    builder: (column) => column,
  );
}

class $$SlideAnalyticsTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $SlideAnalyticsTable,
          SlideAnalyticEntity,
          $$SlideAnalyticsTableFilterComposer,
          $$SlideAnalyticsTableOrderingComposer,
          $$SlideAnalyticsTableAnnotationComposer,
          $$SlideAnalyticsTableCreateCompanionBuilder,
          $$SlideAnalyticsTableUpdateCompanionBuilder,
          (
            SlideAnalyticEntity,
            BaseReferences<
              _$AppDatabase,
              $SlideAnalyticsTable,
              SlideAnalyticEntity
            >,
          ),
          SlideAnalyticEntity,
          PrefetchHooks Function()
        > {
  $$SlideAnalyticsTableTableManager(
    _$AppDatabase db,
    $SlideAnalyticsTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$SlideAnalyticsTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$SlideAnalyticsTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$SlideAnalyticsTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String?> dcrUuid = const Value.absent(),
                Value<String?> doctorUuid = const Value.absent(),
                Value<int> slideIndex = const Value.absent(),
                Value<int> durationSeconds = const Value.absent(),
                Value<DateTime> timestamp = const Value.absent(),
                Value<String> syncStatus = const Value.absent(),
              }) => SlideAnalyticsCompanion(
                id: id,
                dcrUuid: dcrUuid,
                doctorUuid: doctorUuid,
                slideIndex: slideIndex,
                durationSeconds: durationSeconds,
                timestamp: timestamp,
                syncStatus: syncStatus,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String?> dcrUuid = const Value.absent(),
                Value<String?> doctorUuid = const Value.absent(),
                required int slideIndex,
                required int durationSeconds,
                required DateTime timestamp,
                Value<String> syncStatus = const Value.absent(),
              }) => SlideAnalyticsCompanion.insert(
                id: id,
                dcrUuid: dcrUuid,
                doctorUuid: doctorUuid,
                slideIndex: slideIndex,
                durationSeconds: durationSeconds,
                timestamp: timestamp,
                syncStatus: syncStatus,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$SlideAnalyticsTable, SlideAnalyticEntity>(table),
                  BaseReferences<
                    _$AppDatabase,
                    $SlideAnalyticsTable,
                    SlideAnalyticEntity
                  >(db, table, e),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$SlideAnalyticsTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $SlideAnalyticsTable,
      SlideAnalyticEntity,
      $$SlideAnalyticsTableFilterComposer,
      $$SlideAnalyticsTableOrderingComposer,
      $$SlideAnalyticsTableAnnotationComposer,
      $$SlideAnalyticsTableCreateCompanionBuilder,
      $$SlideAnalyticsTableUpdateCompanionBuilder,
      (
        SlideAnalyticEntity,
        BaseReferences<
          _$AppDatabase,
          $SlideAnalyticsTable,
          SlideAnalyticEntity
        >,
      ),
      SlideAnalyticEntity,
      PrefetchHooks Function()
    >;
typedef $$SyncOutboxTableCreateCompanionBuilder =
    SyncOutboxCompanion Function({
      Value<int> id,
      required String entityType,
      required String entityUuid,
      required String payloadJson,
      Value<String> status,
      Value<int> retryCount,
      Value<String?> lastError,
      required DateTime createdAt,
    });
typedef $$SyncOutboxTableUpdateCompanionBuilder =
    SyncOutboxCompanion Function({
      Value<int> id,
      Value<String> entityType,
      Value<String> entityUuid,
      Value<String> payloadJson,
      Value<String> status,
      Value<int> retryCount,
      Value<String?> lastError,
      Value<DateTime> createdAt,
    });

class $$SyncOutboxTableFilterComposer
    extends Composer<_$AppDatabase, $SyncOutboxTable> {
  $$SyncOutboxTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get entityUuid => $composableBuilder(
    column: $table.entityUuid,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get status => $composableBuilder(
    column: $table.status,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<int> get retryCount => $composableBuilder(
    column: $table.retryCount,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get lastError => $composableBuilder(
    column: $table.lastError,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get createdAt => $composableBuilder(
    column: $table.createdAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$SyncOutboxTableOrderingComposer
    extends Composer<_$AppDatabase, $SyncOutboxTable> {
  $$SyncOutboxTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get entityUuid => $composableBuilder(
    column: $table.entityUuid,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get status => $composableBuilder(
    column: $table.status,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<int> get retryCount => $composableBuilder(
    column: $table.retryCount,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get lastError => $composableBuilder(
    column: $table.lastError,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get createdAt => $composableBuilder(
    column: $table.createdAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$SyncOutboxTableAnnotationComposer
    extends Composer<_$AppDatabase, $SyncOutboxTable> {
  $$SyncOutboxTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => column,
  );

  GeneratedColumn<String> get entityUuid => $composableBuilder(
    column: $table.entityUuid,
    builder: (column) => column,
  );

  GeneratedColumn<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => column,
  );

  GeneratedColumn<String> get status =>
      $composableBuilder(column: $table.status, builder: (column) => column);

  GeneratedColumn<int> get retryCount => $composableBuilder(
    column: $table.retryCount,
    builder: (column) => column,
  );

  GeneratedColumn<String> get lastError =>
      $composableBuilder(column: $table.lastError, builder: (column) => column);

  GeneratedColumn<DateTime> get createdAt =>
      $composableBuilder(column: $table.createdAt, builder: (column) => column);
}

class $$SyncOutboxTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $SyncOutboxTable,
          SyncOutboxEntity,
          $$SyncOutboxTableFilterComposer,
          $$SyncOutboxTableOrderingComposer,
          $$SyncOutboxTableAnnotationComposer,
          $$SyncOutboxTableCreateCompanionBuilder,
          $$SyncOutboxTableUpdateCompanionBuilder,
          (
            SyncOutboxEntity,
            BaseReferences<_$AppDatabase, $SyncOutboxTable, SyncOutboxEntity>,
          ),
          SyncOutboxEntity,
          PrefetchHooks Function()
        > {
  $$SyncOutboxTableTableManager(_$AppDatabase db, $SyncOutboxTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$SyncOutboxTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$SyncOutboxTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$SyncOutboxTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> entityType = const Value.absent(),
                Value<String> entityUuid = const Value.absent(),
                Value<String> payloadJson = const Value.absent(),
                Value<String> status = const Value.absent(),
                Value<int> retryCount = const Value.absent(),
                Value<String?> lastError = const Value.absent(),
                Value<DateTime> createdAt = const Value.absent(),
              }) => SyncOutboxCompanion(
                id: id,
                entityType: entityType,
                entityUuid: entityUuid,
                payloadJson: payloadJson,
                status: status,
                retryCount: retryCount,
                lastError: lastError,
                createdAt: createdAt,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String entityType,
                required String entityUuid,
                required String payloadJson,
                Value<String> status = const Value.absent(),
                Value<int> retryCount = const Value.absent(),
                Value<String?> lastError = const Value.absent(),
                required DateTime createdAt,
              }) => SyncOutboxCompanion.insert(
                id: id,
                entityType: entityType,
                entityUuid: entityUuid,
                payloadJson: payloadJson,
                status: status,
                retryCount: retryCount,
                lastError: lastError,
                createdAt: createdAt,
              ),
          withReferenceMapper: (p0) => p0
              .map(
                (e) => (
                  e.readTable<$SyncOutboxTable, SyncOutboxEntity>(table),
                  BaseReferences<
                    _$AppDatabase,
                    $SyncOutboxTable,
                    SyncOutboxEntity
                  >(db, table, e),
                ),
              )
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$SyncOutboxTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $SyncOutboxTable,
      SyncOutboxEntity,
      $$SyncOutboxTableFilterComposer,
      $$SyncOutboxTableOrderingComposer,
      $$SyncOutboxTableAnnotationComposer,
      $$SyncOutboxTableCreateCompanionBuilder,
      $$SyncOutboxTableUpdateCompanionBuilder,
      (
        SyncOutboxEntity,
        BaseReferences<_$AppDatabase, $SyncOutboxTable, SyncOutboxEntity>,
      ),
      SyncOutboxEntity,
      PrefetchHooks Function()
    >;

class $AppDatabaseManager {
  final _$AppDatabase _db;
  $AppDatabaseManager(this._db);
  $$DoctorsTableTableManager get doctors =>
      $$DoctorsTableTableManager(_db, _db.doctors);
  $$ProductsTableTableManager get products =>
      $$ProductsTableTableManager(_db, _db.products);
  $$PromotionalInputsTableTableManager get promotionalInputs =>
      $$PromotionalInputsTableTableManager(_db, _db.promotionalInputs);
  $$DcrsTableTableManager get dcrs => $$DcrsTableTableManager(_db, _db.dcrs);
  $$DcrProductsTableTableManager get dcrProducts =>
      $$DcrProductsTableTableManager(_db, _db.dcrProducts);
  $$DcrInputsTableTableManager get dcrInputs =>
      $$DcrInputsTableTableManager(_db, _db.dcrInputs);
  $$SlideAnalyticsTableTableManager get slideAnalytics =>
      $$SlideAnalyticsTableTableManager(_db, _db.slideAnalytics);
  $$SyncOutboxTableTableManager get syncOutbox =>
      $$SyncOutboxTableTableManager(_db, _db.syncOutbox);
}
