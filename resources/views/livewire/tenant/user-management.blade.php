<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="color: var(--cor-texto-principal); font-size: 1.5em; font-weight: 600;">Gerenciamento de Usuários
        </h1>
        <button wire:click="create" class="btn-primary">
            <i class="fas fa-plus"></i> Novo Usuário
        </button>
    </div>

    <div class="card">
        <div style="margin-bottom: 20px;">
            <input wire:model.debounce.300ms="search" type="text" placeholder="Buscar usuários..."
                style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro);">
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Nome</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Email</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Telefone</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Função</th>
                        <th style="text-align: right; padding: 12px; color: var(--cor-acento);">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td style="padding: 12px; color: var(--cor-texto-claro);">{{ $user->name }}</td>
                            <td style="padding: 12px; color: var(--cor-texto-claro);">{{ $user->email }}</td>
                            <td style="padding: 12px; color: var(--cor-texto-claro);">{{ $user->phone ?? '-' }}</td>
                            <td style="padding: 12px;">
                                @foreach($user->roles as $role)
                                    <span
                                        style="background-color: var(--cor-acento); color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-right: 5px;">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <button wire:click="edit({{ $user->id }})" class="btn-sm btn-secondary"
                                    style="margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->id !== Auth::id())
                                    <button wire:click="delete({{ $user->id }})"
                                        onclick="confirm('Tem certeza que deseja remover este usuário?') || event.stopImmediatePropagation()"
                                        class="btn-sm btn-danger"
                                        style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
        <div
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div class="card" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
                <h2 style="color: var(--cor-acento); margin-bottom: 20px;">
                    {{ $isEditing ? 'Editar Usuário' : 'Novo Usuário' }}
                </h2>

                <form wire:submit.prevent="save">
                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Nome
                            *</label>
                        <input type="text" wire:model="name" required
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        @error('name') <span style="color: #f44336; font-size: 0.8em;">{{ $message }}</span> @enderror
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Email
                            *</label>
                        <input type="email" wire:model="email" required
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        @error('email') <span style="color: #f44336; font-size: 0.8em;">{{ $message }}</span> @enderror
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Telefone</label>
                        <input type="text" wire:model="phone"
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;"
                            placeholder="(11) 99999-9999">
                        @error('phone') <span style="color: #f44336; font-size: 0.8em;">{{ $message }}</span> @enderror
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Senha
                            {{ $isEditing ? '(Deixe em branco para manter)' : '*' }}</label>
                        <input type="password" wire:model="password" {{ !$isEditing ? 'required' : '' }}
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        @error('password') <span style="color: #f44336; font-size: 0.8em;">{{ $message }}</span> @enderror
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Permissões
                            *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            @foreach($roles as $role)
                                <label
                                    style="display: flex; align-items: center; cursor: pointer; color: var(--cor-texto-claro);">
                                    <input type="checkbox" wire:model="selected_roles" value="{{ $role->name }}"
                                        style="margin-right: 8px;">
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('selected_roles') <span style="color: #f44336; font-size: 0.8em;">Selecione pelo menos uma
                        permissão.</span> @enderror
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>