@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Admin Message Panel</h4>

    <div class="row">
        <div class="col-md-4">
            <h5>All Users</h5>
            <ul class="list-group" id="userList">
                <li class="list-group-item text-muted">Loading users...</li>
            </ul>
        </div>
        @php
            $user = session('user'); 
            $role_id = $user['role'];
        @endphp

        <div class="col-md-8">
            <div id="chatBox" class="border rounded p-3" style="height: 500px; overflow-y: scroll;">
                <div id="messages"></div>
            </div>
            
                <form id="chatForm" class="mt-3" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="sender_id" value="3">
                    <input type="hidden" name="receiver_id" id="receiver_id">
                    <div class="input-group">
                          @if($role_id == 1)
                        <input type="text" name="message" class="form-control" placeholder="Type a message..." />
                        <input type="hidden" name="file" class="form-control" />
                       
                                <button type="submit" class="btn btn-primary">Send</button>
                        @endif
                    </div>
                </form>
       
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const senderId = 3;
    let selectedUserId = null;

    function loadUsers() {
        fetch(`/admin/chat/users`)
            .then(res => res.json())
            .then(users => {
                const list = document.getElementById('userList');
                list.innerHTML = '';
                users.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = user.name;
                
                    if (user.id === selectedUserId) {
                        li.classList.add('active');
                    }
                
                    li.onclick = () => {
                        selectedUserId = user.id;
                        document.getElementById('receiver_id').value = selectedUserId;
                
                        // Remove 'active' from all list items
                        document.querySelectorAll('#userList .list-group-item').forEach(el => {
                            el.classList.remove('active');
                        });
                
                        // Add 'active' to the clicked item
                        li.classList.add('active');
                
                        fetchMessages();
                    };
                
                    list.appendChild(li);
                });

            })
            .catch(err => {
                document.getElementById('userList').innerHTML = `<li class="list-group-item text-danger">Error loading users</li>`;
                console.error(err);
            });
    }

    function fetchMessages() {
        if (!selectedUserId) return;
        fetch(`/admin/chat/messages?user_1=${senderId}&user_2=${selectedUserId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('messages').innerHTML = html;
                document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;
            });
    }

    document.getElementById('chatForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);

        fetch(`/admin/chat/send`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(() => {
            e.target.reset();
            fetchMessages();
        });
    });

    loadUsers();
</script>
@endsection
