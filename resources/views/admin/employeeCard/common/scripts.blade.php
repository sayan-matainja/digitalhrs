<script>
    document.querySelectorAll('.graph-type').forEach(radio => {
        radio.addEventListener('change', function() {
            const wrapper = document.getElementById('graph-content-wrapper');
            wrapper.style.display = this.value === 'none' ? 'none' : 'block';

            document.getElementById('graph_content').required = this.value !== 'none';
        });
    });

    document.getElementById('graph_content')?.addEventListener('change', function() {
        const customInput = document.getElementById('custom-graph-text');
        customInput.style.display = this.value === 'custom_text' ? 'block' : 'none';
    });

    document.querySelectorAll('.field-select').forEach(sel => {
        sel.addEventListener('change', function() {
            if (this.value) {
                document.querySelectorAll('.field-select').forEach(other => {
                    if (other !== this && other.value === this.value) {
                        other.value = '';
                        alert('Field already selected!');
                    }
                });
            }
        });
    });

    document.getElementById('idCardForm').onsubmit = function(e) {
        const values = Array.from(document.querySelectorAll('.field-select'))
            .map(s => s.value)
            .filter(v => v);

        const unique = [...new Set(values)];

        if (values.length === 0 || values.length > 4 || values.length !== unique.length) {
            e.preventDefault();
            document.getElementById('field-error').classList.remove('d-none');
            alert('Please select 1 to 4 unique extra fields.');
            return false;
        }
        document.getElementById('field-error').classList.add('d-none');
    };
</script>
