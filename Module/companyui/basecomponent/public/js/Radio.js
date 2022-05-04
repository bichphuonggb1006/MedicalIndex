window.radioBoxID = 0;

class Radio extends PureComponent {
    constructor(props) {
        super(props);
        this.elmID = this.props.id ? this.props.id : 'rdo-' + window.radioBoxID++;

        const checked = 'checked' in props ? props.checked : props.defaultChecked;
        const disabled = props.disabled == 'true' || props.disabled && (props.disabled != 'false') ? true : false;

        this.state = {
            'checked': checked,
            'disabled': disabled,
            'label': this.props.label
        };

    }

    isChecked() {
        return this.props.checked;
    }

    static defaultProps = {
        className: '',
        style: {},
        type: 'checkbox',
        onFocus() {
        },
        onBlur() {
        },
        onChange() {
        }
    };

    componentWillReceiveProps(nextProps) {
        if ('checked' in nextProps) {
            this.setState({
                checked: nextProps.checked,
            });
        }
        if ('disabled' in nextProps) {
            this.setState({
                disabled: nextProps.disabled,
            });
        }
        if ('label' in nextProps) {
            this.setState({
                label: nextProps.label,
            });
        }
    }

    handleClick() {
        var that = this;
        var chk = $('#' + that.elmID);

        if (this.props.onClick) {
            this.props.onClick();
        }
    }

    render() {
        return (
            <div className="radio">
                <input id={this.elmID} type="radio" className={this.props.className}
                       ref={(elm) => {
                           this.radiobox = elm;
                       }}
                       onClick={() => {
                           this.handleClick();
                       }}
                       checked={'checked' in this.props ? this.props.checked : false}
                       readOnly={true}
                       disabled={this.state.disabled}
                />
                <label htmlFor={this.elmID}>{this.state.label}</label>
            </div>
        );
    }
}