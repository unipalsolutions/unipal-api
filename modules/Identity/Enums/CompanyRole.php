<?php

namespace Modules\Identity\Enums;

enum CompanyRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Member = 'member';

    /** @return list<Ability> */
    public function abilities(): array
    {
        return match ($this) {
            self::Owner, self::Admin => Ability::cases(),
            self::Manager => [Ability::CrmView, Ability::CrmManage, Ability::ProjectsView, Ability::ProjectsManage],
            self::Member => [Ability::CrmView, Ability::ProjectsView, Ability::ProjectsManage],
        };
    }

    public function allows(Ability $ability): bool
    {
        return in_array($ability, $this->abilities(), true);
    }

    /** Owners and admins of the parent company act in every company of the group. */
    public function spansGroup(): bool
    {
        return $this === self::Owner || $this === self::Admin;
    }
}
