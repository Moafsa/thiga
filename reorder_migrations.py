import os
import re
from collections import defaultdict, deque

MIGRATIONS_DIR = 'database/migrations'

def parse_migration(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find created tables
    created_tables = re.findall(r"Schema::create\(['\"]([^'\"]+)['\"]", content)
    
    # Find dependencies
    dependencies = set()
    
    # Explicit constrained('table_name')
    explicit_deps = re.findall(r"->constrained\(['\"]([^'\"]+)['\"]\)", content)
    dependencies.update(explicit_deps)
    
    # Implicit constrained() - needs to look at the column name
    # e.g. foreignId('user_id')->constrained()
    implicit_matches = re.findall(r"foreignId\(['\"]([^'\"]+)_id['\"]\)(?:->\w+\([^)]*\))*->constrained\(\)", content)
    for match in implicit_matches:
        # pluralize naive (just append s, handle 'y' -> 'ies' if needed)
        if match.endswith('y'):
            table = match[:-1] + 'ies'
        elif match.endswith('ss'):
            table = match + 'es'
        else:
            table = match + 's'
        dependencies.add(table)
        
    # Explicit foreign('col')->references('id')->on('table')
    on_deps = re.findall(r"->on\(['\"]([^'\"]+)['\"]\)", content)
    dependencies.update(on_deps)

    return created_tables, list(dependencies)

def main():
    files = sorted([f for f in os.listdir(MIGRATIONS_DIR) if f.endswith('.php')])
    
    file_info = {}
    table_to_file = {}
    
    # First pass: parse all files
    for f in files:
        filepath = os.path.join(MIGRATIONS_DIR, f)
        creates, deps = parse_migration(filepath)
        file_info[f] = {
            'creates': creates,
            'deps': deps,
            'original_order': files.index(f)
        }
        for t in creates:
            table_to_file[t] = f

    # Build dependency graph between files
    graph = defaultdict(list)
    in_degree = defaultdict(int)
    
    for f in files:
        in_degree[f] = 0 # Initialize all files in in_degree
        
    for f in files:
        deps = file_info[f]['deps']
        for dep_table in deps:
            # If the table is created in another file, this file depends on that file
            dep_file = table_to_file.get(dep_table)
            if dep_file and dep_file != f:
                graph[dep_file].append(f)
                in_degree[f] += 1
                
    # Topological sort (Kahn's algorithm)
    # To maintain as much of the original order as possible, we use a priority queue
    # based on the original order.
    import heapq
    queue = []
    for f in files:
        if in_degree[f] == 0:
            heapq.heappush(queue, (file_info[f]['original_order'], f))
            
    sorted_files = []
    while queue:
        _, current_file = heapq.heappop(queue)
        sorted_files.append(current_file)
        
        for neighbor in graph[current_file]:
            in_degree[neighbor] -= 1
            if in_degree[neighbor] == 0:
                heapq.heappush(queue, (file_info[neighbor]['original_order'], neighbor))
                
    if len(sorted_files) != len(files):
        print(f"Cycle detected or missing files! Sorted: {len(sorted_files)}, Total: {len(files)}")
        # Fallback: find files not in sorted_files
        missing = set(files) - set(sorted_files)
        print("Missing files:", missing)
        return

    # Rename files with new sequential timestamps
    import datetime
    
    base_time = datetime.datetime(2024, 1, 1, 0, 0, 0)
    
    for idx, f in enumerate(sorted_files):
        # Extract the descriptive part of the filename
        parts = f.split('_')
        # Original format: YYYY_MM_DD_HHMMSS_description.php
        # Sometimes: YYYY_MM_DD_HHMMSS_description.php
        description_parts = parts[4:]
        if len(description_parts) == 0:
            description_parts = parts[1:] # Fallback
            
        description = "_".join(description_parts)
        
        new_time = base_time + datetime.timedelta(minutes=idx)
        new_prefix = new_time.strftime("%Y_%m_%d_%H%M%S")
        new_filename = f"{new_prefix}_{description}"
        
        if f != new_filename:
            old_path = os.path.join(MIGRATIONS_DIR, f)
            new_path = os.path.join(MIGRATIONS_DIR, new_filename)
            print(f"Renaming {f} -> {new_filename}")
            os.rename(old_path, new_path)
            
    print("All migrations reordered successfully!")

if __name__ == '__main__':
    main()
