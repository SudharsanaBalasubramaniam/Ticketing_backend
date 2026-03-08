# Laravel Support Ticketing System - Interview Guide

## Project Overview

**Laravel Support Ticketing** is a full-featured support/help desk management system built with **Laravel 8** that allows users to create support tickets, track their status in real-time, and communicate with support agents through comments and attachments.

### Key Technology Stack
- **Backend**: Laravel 8 (PHP Framework)
- **Database**: MySQL
- **Media Management**: Spatie MediaLibrary
- **Authentication**: Laravel Passport (OAuth 2.0)
- **Authorization**: Role-Based Access Control (RBAC)
- **Email**: Notifications & Queueing

---

## Project Architecture & Core Components

### 1. **Database Layer & Models**

#### **Ticket Model** (`app/Ticket.php`)
- **Core Entity**: Represents a support ticket
- **Key Attributes**:
  - `id` - Primary Key
  - `title` - Ticket subject
  - `content` - Initial issue description
  - `author_name` & `author_email` - Ticket creator info
  - `status_id` - Current status (Open, In Progress, Closed)
  - `priority_id` - Urgency level (Low, Medium, High)
  - `category_id` - Issue category (Support, Bug, Feature)
  - `assigned_to_user_id` - Agent assigned to ticket

**Key Relationships**:
```php
comments()          // One-to-Many: Ticket has many Comments
status()            // Belongs-To: Link to Status lookup
priority()          // Belongs-To: Link to Priority lookup
category()          // Belongs-To: Link to Category lookup
assigned_to_user()  // Belongs-To: Link to assigned Agent/User
```

**Key Features**:
- Uses `SoftDeletes` trait (logical deletion)
- Implements `HasMedia` (file attachment support)
- Uses `Auditable` trait (automatic audit logging)
- Uses `InteractsWithMedia` (Spatie MediaLibrary)

#### **User Model** (`app/User.php`)
- **Roles**: Admin, Agent, Customer
- **Key Relationships**:
  ```php
  tickets()     // Agent's assigned tickets
  comments()    // User's comments on tickets
  roles()       // Belongs-To-Many roles
  ```
- **Key Methods**:
  - `isAdmin()` - Check if user is Admin (contains role_id=1)
  - Dynamic password hashing via `setPasswordAttribute()`

#### **Comment Model** (`app/Comment.php`)
- **Purpose**: Track ticket updates and communications
- **Attributes**: user_id, ticket_id, comment_text, author_name, author_email
- **Relationships**: 
  - `ticket()` - Parent ticket
  - `user()` - Comment author

#### **Other Core Models**
- **Role** - Define user roles (Admin, Agent)
- **Permission** - Define access permissions
- **Status** - Ticket statuses (Open, In Progress, Closed)
- **Priority** - Severity levels (Low, Medium, High)
- **Category** - Issue categories
- **AuditLog** - Track all model changes

---

### 2. **Authentication & Authorization**

#### **Role-Based Access Control (RBAC)**
```php
// Database Structure:
// roles table: id, title
// users table: includes soft_deletes
// role_user pivot table: user_id, role_id

// Role IDs:
// 1 = Admin (Full system access)
// 2 = Agent (Support staff, view assigned tickets)
// 3 = Customer (Can create and view own tickets)
```

#### **User Authentication**
- Uses Laravel's built-in authentication
- Credentials:
  - Admin: `admin@admin.com` / `password`
  - Agent: `agent1@agent1.com` / `password`

#### **Authorization Gate & Policies**
- Gate-based authorization for admin actions
- Permissions linked to roles via `permission_role` pivot table
- Dashboard access restricted based on roles

---

### 3. **Key Business Logic & Traits**

#### **Auditable Trait** (`app/Traits/Auditable.php`)
**Purpose**: Automatic audit logging of all model changes

**How it works**:
```php
// Listens to Eloquent events:
static::created()   // Logs new record creation
static::updated()   // Logs record updates
static::deleted()   // Logs record deletion

// Creates AuditLog entry with:
- description    (action: created/updated/deleted)
- subject_id     (model's primary key)
- subject_type   (model class name)
- user_id        (authenticated user)
- properties     (full model data)
- host           (IP address)
```

**Interview Question**: "How would you track all changes to a ticket?"
**Answer**: "The `Auditable` trait automatically logs every ticket creation, update, and deletion to the `audit_logs` table through Eloquent event listeners."

#### **AgentScope** (`app/Scopes/AgentScope.php`)
**Purpose**: Global query scope to filter data for Agents

**Logic**:
```php
// If user is an Agent (role_id=2), they ONLY see their assigned tickets:
if($user->roles->contains(2)) {  // role_id=2 = Agent
    $builder->where('assigned_to_user_id', $user->id);
}
// Admins see all tickets
```

**Use Case**: Agent logs in → sees only tickets assigned to them automatically

#### **TicketActionObserver** (`app/Observers/TicketActionObserver.php`)
**Purpose**: Handle side effects when tickets are created/updated

**Triggered Actions**:
```php
// When ticket is CREATED:
1. Send email notification to ALL ADMINS
2. Alert them: "New ticket has been created!"

// When ticket is UPDATED (specifically assigned_to_user_id changes):
1. Check if ticket assignment changed: isDirty('assigned_to_user_id')
2. Send notification to newly assigned Agent
3. Use AssignedTicketNotification
```

---

### 4. **Controller Layer**

#### **TicketController** (`app/Http/Controllers/TicketController.php`)
**Public Actions** (Guest/Customer):

1. **create()** - Show ticket creation form
   ```php
   return view('tickets.create');
   ```

2. **store(Request $request)** - Save new ticket
   - Validation: title, content, author_name (email)
   - Auto-assign: category_id=1, status_id=1 (Open), priority_id=1
   - Handle attachments via MediaLibrary
   - Trigger Observer notifications automatically

3. **show(Ticket $ticket)** - Display ticket + comments
   ```php
   return view('tickets.show', compact('ticket'));
   ```
   - Load comments relationship: `$ticket->load('comments')`

4. **storeComment(Request $ticket)** - Add comment to ticket
   - Create Comment: `$ticket->comments()->create([...])`
   - Trigger: `$ticket->sendCommentNotification($comment)`

#### **Key Method in Ticket Model: sendCommentNotification()**
**Complex Logic** - Determines who receives notification:

```php
$users = User::where(function ($q) {
    // Get Agents AND:
    $q->whereHas('roles', function ($q) { 
        $q->where('title', 'Agent');
    })
    ->where(function ($q) {
        // - Already commented on this ticket OR
        $q->whereHas('comments', function ($q) {
            $q->whereTicketId($this->id);
        })
        // - Assigned to this ticket
        ->orWhereHas('tickets', function ($q) {
            $q->whereId($this->id);
        }); 
    });
})
// If no assigned agent/no user comment, notify ALL ADMINS
->when(!$comment->user_id && !$this->assigned_to_user_id, function ($q) {
    $q->orWhereHas('roles', function ($q) {
        $q->where('title', 'Admin');
    });
})
// Don't notify the commenter themselves
->when($comment->user, function ($q) use ($comment) {
    $q->where('id', '!=', $comment->user_id);
})
->get();
```

#### **Admin TicketsController** (`app/Http/Controllers/Admin/TicketsController.php`)
Full CRUD for admin/agents:
- `index()` - List all/assigned tickets (with AgentScope filtering)
- `create()` - Create ticket (admin only)
- `edit()` - Update ticket details
- `delete()` / `massDestroy()` - Soft delete tickets
- `storeComment()` - Add agent comments
- `storeMedia()` - Upload attachments

---

### 5. **Important Functionalities to Know**

| Feature | Implementation | Files |
|---------|----------------|-------|
| **Ticket Management** | Full CRUD with soft deletes | TicketController, Ticket model |
| **Comments/Threading** | Nested comments on tickets | Comment model, storeComment() |
| **File Attachments** | Spatie MediaLibrary integration | registerMediaConversions(), storeMedia() |
| **Role-Based Access** | RBAC with permissions | Role, Permission, AgentScope |
| **Audit Logging** | Track all changes | Auditable trait, AuditLog model |
| **Email Notifications** | Send alerts on events | Observers, Notifications (Mailable) |
| **Email Auto-Filtering** | Only notify relevant agents | sendCommentNotification() logic |
| **Soft Deletes** | Logical deletion | SoftDeletes trait |
| **Password Encryption** | Auto hash on set | setPasswordAttribute() |
| **Ticket Filtering** | Query scopes | scopeFilterTickets(), AgentScope |

---

## Database Schema (Key Tables)

```sql
-- Ticket Management
tickets
├── id, title, content
├── status_id (FK → statuses.id)
├── priority_id (FK → priorities.id)
├── category_id (FK → categories.id)
├── assigned_to_user_id (FK → users.id)
├── author_name, author_email
└── created_at, updated_at, deleted_at

comments
├── id, ticket_id (FK)
├── user_id (FK → users.id)
├── comment_text
├── author_name, author_email
└── created_at, updated_at, deleted_at

-- User Management
users
├── id, name, email, password
├── created_at, updated_at, deleted_at
└── email_verified_at

roles
├── id, title (Admin, Agent, Customer)
└── created_at, updated_at, deleted_at

permissions
├── id, title
└── created_at, updated_at, deleted_at

role_user (Pivot)
├── user_id, role_id

permission_role (Pivot)
├── permission_id, role_id

-- Audit Trail
audit_logs
├── id
├── description (created/updated/deleted)
├── subject_id, subject_type (polymorphic)
├── user_id (who made change)
├── properties (changed data)
├── host (IP address)
└── created_at

-- Media
media
├── id, model_type (polymorphic)
├── model_id, collection_name
├── name, file_name, disk, size
└── created_at, updated_at
```

---

## Routing Structure

```php
// PUBLIC ROUTES (No Auth)
GET  /                        // Redirect to ticket creation
GET  /tickets/create          // Create ticket form
POST /tickets                 // Submit new ticket
GET  /tickets/{id}            // View ticket + comments
POST /tickets/{id}/comment    // Add comment
POST /tickets/media           // Upload attachment

// AUTHENTICATION ROUTES
POST /login, /register, /password/reset, etc.

// ADMIN ROUTES (Requires Auth + Appropriate Role)
GET  /admin                                   // Dashboard
GET  /admin/tickets                          // List tickets (filtered by role)
GET  /admin/tickets/{id}/edit                // Edit ticket
PUT  /admin/tickets/{id}                     // Update ticket
DELETE /admin/tickets/{id}                   // Soft delete
PUT  /admin/tickets/destroy                  // Mass delete
POST /admin/tickets/{id}/comment             // Add admin comment
POST /admin/tickets/media                    // Upload media
GET  /admin/permissions, /roles, /users      // CRUD for setup
GET  /admin/audit-logs                       // View audit trail
```

---

## Common Interview Questions & Answers

### Q1: How does the application prevent agents from seeing other agents' tickets?
**Answer**: The `AgentScope` global scope filters queries. When an Agent logs in, any ticket query automatically adds `WHERE assigned_to_user_id = {agent_id}`. This is applied globally in the Ticket model's `boot()` method.

### Q2: How are notifications handled when someone comments on a ticket?
**Answer**: The `sendCommentNotification()` method in Ticket model:
1. Queries all Agents involved with the ticket
2. Adds Admins if no agent is assigned
3. Excludes the commenter
4. Sends via `Notification::send()` - routing to email (or log in development)

### Q3: How is audit logging implemented?
**Answer**: The `Auditable` trait uses Eloquent events (created, updated, deleted) to automatically create AuditLog entries. This captures what changed, who changed it, when, and from where (IP).

### Q4: What's the purpose of soft deletes?
**Answer**: Soft deletes don't remove records; they mark them with a `deleted_at` timestamp. This preserves data for compliance/auditing while hiding "deleted" records from queries (unless explicitly included via `withTrashed()`).

### Q5: How are attachments handled?
**Answer**: Using Spatie MediaLibrary. In TicketController::store(), after creating a ticket:
```php
// Move from temp storage to media collection
foreach ($request->input('attachments', []) as $file) {
    $ticket->addMedia(storage_path('tmp/uploads/' . $file))
           ->toMediaCollection('attachments');
}
```
Media relationships and conversions (thumbnails) are auto-managed.

### Q6: How do you scale ticket assignment notifications?
**Answer**: Currently immediate. For scale, use:
- **Queue**: `dispatch(new SendNotification($ticket))->queue()`
- **Rate Limiting**: Prevent notification storms
- **Batching**: Group notifications if multiple changes occur
- **Redis**: For caching notification preferences

---

## Key Takeaways for Interview

1. **Architecture**: Clean separation - Models (business logic), Controllers (HTTP handling), Traits (reusable patterns), Observers (event handling)

2. **Security**: 
   - RBAC with role-based query scoping
   - Password hashing
   - Foreign key constraints
   - Audit trails for compliance

3. **Design Patterns Used**:
   - **Observer Pattern**: TicketActionObserver
   - **Trait Pattern**: Auditable, MediaUploadingTrait
   - **Global Scope Pattern**: AgentScope
   - **Factory Pattern**: Model factories for testing

4. **Scalability Considerations**:
   - Soft deletes for data preservation
   - Media library for flexible file handling
   - Audit logs for compliance
   - Ready for queue integration

5. **Code Quality**:
   - Validation in controllers
   - Type hints in method signatures
   - Eloquent relationships over raw queries
   - Reusable traits for cross-cutting concerns

---

## Quick Commands Reference

```bash
# Setup & Migration
php artisan migrate
php artisan db:seed

# Create New Ticket
POST /tickets with: title, content, author_name, author_email

# View Tickets
GET /admin/tickets           # Admin sees all
GET /admin/tickets           # Agent sees only assigned

# Add Comment
POST /tickets/{id}/comment with: comment_text

# View Audit Trail
GET /admin/audit-logs        # Track all system changes
```

---

**Good Luck with Your Interview!** 🎉

Focus on explaining:
1. How data flows through the application
2. How security is maintained (RBAC, scopes)
3. How notifications work end-to-end
4. Your understanding of design patterns
