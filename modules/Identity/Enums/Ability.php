<?php

namespace Modules\Identity\Enums;

/** What a member may do inside one company. Roles grant abilities; modules check abilities, never roles. */
enum Ability: string
{
    case CrmView = 'crm.view';
    case CrmManage = 'crm.manage';
    case ProjectsView = 'projects.view';
    case ProjectsManage = 'projects.manage';
    case MembersManage = 'members.manage';
}
