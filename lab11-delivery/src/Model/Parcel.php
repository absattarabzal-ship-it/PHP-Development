    public function withStatus(string $status): self
    {
        $history = $this->history;
        $history[] = ['status' => $this->status, 'at' => date('Y-m-d H:i:s')];

        $deliveredAt = $this->deliveredAt;

        // Имитация реального времени в пути (15-60 минут)
        if ($status === 'delivered' && $deliveredAt === null) {
            $travelMinutes = rand(15, 60);
            $deliveredAt = date('Y-m-d H:i:s', strtotime($this->createdAt . " +{$travelMinutes} minutes"));
        }

        return new self(
            tracking: $this->tracking,
            recipient: $this->recipient,
            address: $this->address,
            items: $this->items,
            weightKg: $this->weightKg,
            zone: $this->zone,
            status: $status,
            express: $this->express,
            courier: $this->courier,
            createdAt: $this->createdAt,
            estimatedArrival: $this->estimatedArrival,
            deliveredAt: $deliveredAt,
            history: $history,
        );
    }