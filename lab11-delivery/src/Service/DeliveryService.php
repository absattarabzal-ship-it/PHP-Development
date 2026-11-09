// В методе register() замените создание $newParcel на это:
        $newParcel = new Parcel(
            tracking: $parcel->tracking,
            recipient: $parcel->recipient,
            address: $parcel->address,
            items: $parcel->items,
            weightKg: $parcel->weightKg,
            zone: $parcel->zone,
            status: 'registered',
            express: $parcel->express,
            courier: $parcel->courier,
            createdAt: $createdAt,
            estimatedArrival: $estimatedArrival,
        );