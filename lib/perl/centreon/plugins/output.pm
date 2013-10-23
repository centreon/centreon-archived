
package centreon::plugins::output;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;
    # $options->{options} = options object
    if (!defined($options{options})) {
        print "Class Output: Need to specify 'options' argument to load.\n";
        exit 3;
    }

    $options{options}->add_options(arguments =>
                                {
                                  "ignore-perfdata"         => { name => 'ignore_perfdata' },
                                  "verbose"                 => { name => 'verbose' },
                                  "opt-exit:s"              => { name => 'opt_exit', default => 'unknown' },
                                  "output-xml"              => { name => 'output_xml' },
                                  "disco-format"            => { name => 'disco_format' },
                                  "disco-show"              => { name => 'disco_show' },
                                });
    %{$self->{option_results}} = ();

    $self->{option_msg} = [];
    
    $self->{is_output_xml} = 0;
    $self->{errors} = {OK => 0, WARNING => 1, CRITICAL => 2, UNKNOWN => 3, PENDING => 4};
    $self->{myerrors} = {0 => "OK", 1 => "WARNING", 3 => "CRITICAL", 7 => "UNKNOWN"};
    $self->{myerrors_mask} = {CRITICAL => 3, WARNING => 1, UNKNOWN => 7, OK => 0};
    $self->{global_short_concat_outputs} = {OK => undef, WARNING => undef, CRITICAL => undef, UNKNOWN => undef, UNQUALIFIED_YET => undef};
    $self->{global_short_outputs} = {OK => [], WARNING => [], CRITICAL => [], UNKNOWN => [], UNQUALIFIED_YET => []};
    $self->{global_long_output} = [];
    $self->{perfdatas} = [];
    $self->{global_status} = 0;

    $self->{disco_elements} = [];
    $self->{disco_entries} = [];

    $self->{plugin} = '';
    $self->{mode} = '';

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    # $options{option_results} = ref to options result

    %{$self->{option_results}} = %{$options{option_results}};
    $self->{option_results}->{opt_exit} = lc($self->{option_results}->{opt_exit});
    if (!$self->is_litteral_status(status => $self->{option_results}->{opt_exit})) {
        $self->add_option_msg(short_msg => "Unknown value '" . $self->{option_results}->{opt_exit}  . "' for --opt-exit.");
        $self->option_exit(exit_litteral => 'unknown');
    }
    # Go in XML Mode
    if ($self->is_disco_show() || $self->is_disco_format()) {
        $self->{option_results}->{output_xml} = 1;
    }
}

sub add_option_msg {
    my ($self, %options) = @_;
    # $options{short_msg} = string msg
    # $options{long_msg} = string msg
    $options{severity} = 'UNQUALIFIED_YET';
    
    $self->output_add(%options);
}

sub set_status {
    my ($self, %options) = @_;
    # $options{exit_litteral} = string litteral exit

    # Nothing to do for 'UNQUALIFIED_YET'
    if (!$self->{myerrors_mask}->{uc($options{exit_litteral})}) {
        return ;
    }
    $self->{global_status} |= $self->{myerrors_mask}->{uc($options{exit_litteral})};
}

sub output_add {
    my ($self, %params) = @_;
    my %args = (
                severity => 'OK',
                separator => ' - ',
                short_msg => undef,
                long_msg => undef
                );
    my $options = {%args, %params};
    
    if (defined($options->{short_msg})) {
    chomp $options->{short_msg};
        if (defined($self->{global_short_concat_outputs}->{uc($options->{severity})})) {
            $self->{global_short_concat_outputs}->{uc($options->{severity})} .= $options->{separator} . $options->{short_msg};
        } else {
            $self->{global_short_concat_outputs}->{uc($options->{severity})} = $options->{short_msg};
        }
        
        push @{$self->{global_short_outputs}->{uc($options->{severity})}}, $options->{short_msg};
        $self->set_status(exit_litteral => $options->{severity});
    }
    if (defined($options->{long_msg})) {
        chomp $options->{long_msg};
        push @{$self->{global_long_output}}, $options->{long_msg};
    }
}

sub perfdata_add {
    my ($self, %options) = @_;
    my $perfdata = {'label' => '', 'value' => '', unit => '', warning => '', critical => '', min => '', max => ''}; 
    $perfdata = {%$perfdata, %options};
    push @{$self->{perfdatas}}, $perfdata;
}

sub output_xml {
    my ($self, %options) = @_;
    my ($child_plugin_name, $child_plugin_mode, $child_plugin_exit, $child_plugin_output, $child_plugin_perfdata); 

    my $root = $self->{xml_output}->createElement('plugin');
    $self->{xml_output}->setDocumentElement($root);

    $child_plugin_name = $self->{xml_output}->createElement("name");
    $child_plugin_name->appendText($self->{plugin});

    $child_plugin_mode = $self->{xml_output}->createElement("mode");
    $child_plugin_mode->appendText($self->{mode});

    $child_plugin_exit = $self->{xml_output}->createElement("exit");
    $child_plugin_exit->appendText($options{exit_litteral});

    $child_plugin_output = $self->{xml_output}->createElement("outputs");
    $child_plugin_perfdata = $self->{xml_output}->createElement("perfdatas");

    $root->addChild($child_plugin_name);
    $root->addChild($child_plugin_mode);
    $root->addChild($child_plugin_exit);
    $root->addChild($child_plugin_output);
    $root->addChild($child_plugin_perfdata);

    foreach my $code_litteral (keys %{$self->{global_short_outputs}}) {
    foreach (@{$self->{global_short_outputs}->{$code_litteral}}) {
            my ($child_output, $child_type, $child_msg, $child_exit);
            my $lcode_litteral = ($code_litteral eq 'UNQUALIFIED_YET' ? uc($options{exit_litteral}) : $code_litteral);

            $child_output = $self->{xml_output}->createElement("output");
            $child_plugin_output->addChild($child_output);

            $child_type = $self->{xml_output}->createElement("type");
            $child_type->appendText(1); # short

            $child_msg = $self->{xml_output}->createElement("msg");
            $child_msg->appendText(($options{nolabel} == 0 ? ($lcode_litteral . ': ') : '') . $_);
            $child_exit = $self->{xml_output}->createElement("exit");
            $child_exit->appendText($lcode_litteral);

            $child_output->addChild($child_type);
            $child_output->addChild($child_exit);
            $child_output->addChild($child_msg);
        }
    }

    if (defined($self->{option_results}->{verbose}) || defined($options{force_long_output})) {
    foreach (@{$self->{global_long_output}}) {
            my ($child_output, $child_type, $child_msg);
        
            $child_output = $self->{xml_output}->createElement("output");
            $child_plugin_output->addChild($child_output);

            $child_type = $self->{xml_output}->createElement("type");
            $child_type->appendText(2); # long

            $child_msg = $self->{xml_output}->createElement("msg");
            $child_msg->appendText($_);

            $child_output->addChild($child_type);
            $child_output->addChild($child_msg);
        }
    }

    if (!defined($self->{option_results}->{ignore_perfdata}) && !defined($options{force_ignore_perfdata})) {
        foreach (@{$self->{perfdatas}}) {
            my ($child_perfdata);
            $child_perfdata = $self->{xml_output}->createElement("perfdata");
            $child_plugin_perfdata->addChild($child_perfdata);
            foreach my $key (keys %$_) {
                my $child = $self->{xml_output}->createElement($key);
                $child->appendText($_->{$key});
                $child_perfdata->addChild($child);
            }
        }
    }

    print $self->{xml_output}->toString(1);
}

sub output_txt {
    my ($self, %options) = @_;

    if (defined($self->{global_short_concat_outputs}->{UNQUALIFIED_YET})) {
        $self->output_add(severity => uc($options{exit_litteral}), short_msg => $self->{global_short_concat_outputs}->{UNQUALIFIED_YET});
    }

    if (defined($self->{global_short_concat_outputs}->{CRITICAL})) {
        print (($options{nolabel} == 0 ? 'CRITICAL: ' : '') . $self->{global_short_concat_outputs}->{CRITICAL} . " ");
    }
    if (defined($self->{global_short_concat_outputs}->{WARNING})) {
        print (($options{nolabel} == 0 ? 'WARNING: ' : '') . $self->{global_short_concat_outputs}->{WARNING} . " ");
    }
    if (defined($self->{global_short_concat_outputs}->{UNKNOWN})) {
        print (($options{nolabel} == 0 ? 'UNKNOWN: ' : '') . $self->{global_short_concat_outputs}->{UNKNOWN} . " ");
    }
    if (uc($options{exit_litteral}) eq 'OK') {
        print (($options{nolabel} == 0 ? 'OK: ' : '') . $self->{global_short_concat_outputs}->{OK});
    }

    if (defined($options{force_ignore_perfdata}) || defined($self->{option_results}->{ignore_perfdata})) {
        print "\n";
    } else {
        print "|";
        foreach (@{$self->{perfdatas}}) {
            print " '" . $_->{label} . "'=" . $_->{value} . $_->{unit} . ";" . $_->{warning} . ";" . $_->{critical} . ";" . $_->{min} . ";" . $_->{max} . ";";
        }
        print "\n";
    }
    
    if (defined($self->{option_results}->{verbose}) || defined($options{force_long_output})) {
        if (scalar(@{$self->{global_long_output}})) {
            print join("\n", @{$self->{global_long_output}});
            print "\n";
        }
    }
}

sub display {
    my ($self, %options) = @_;

    if (defined($self->{option_results}->{output_xml})) {
        $self->create_xml_document();
        if ($self->{is_output_xml}) {
            $self->output_xml(exit_litteral => $self->get_litteral_status());
        } else {
            $self->output_txt(exit_litteral => $self->get_litteral_status());
        }
    } else {
        $self->output_txt(exit_litteral => $self->get_litteral_status());
    }
}

sub option_exit {
    my ($self, %options) = @_;
    # $options{exit_litteral} = string litteral exit
    # $options{nolabel} = interger label display
    my $exit_litteral = defined($options{exit_litteral}) ? $options{exit_litteral} : $self->{option_results}->{opt_exit};
    my $nolabel = defined($options{nolabel}) ? 1 : 0;

    if (defined($self->{option_results}->{output_xml})) {
        $self->create_xml_document();
        if ($self->{is_output_xml}) {
            $self->output_xml(exit_litteral => $exit_litteral, nolabel => $nolabel, force_ignore_perfdata => 1, force_long_output => 1);
        } else {
            $self->output_txt(exit_litteral => $exit_litteral, nolabel => $nolabel, force_ignore_perfdata => 1, force_long_output => 1);
        }
    } else {
        $self->output_txt(exit_litteral => $exit_litteral, nolabel => $nolabel, force_ignore_perfdata => 1, force_long_output => 1);
    }
    
    $self->exit(exit_litteral => $exit_litteral);
}

sub exit {
    my ($self, %options) = @_;
    # $options{exit_litteral} = exit
    
    if (defined($options{exit_litteral})) {
        exit $self->{errors}->{uc($options{exit_litteral})};
    }
    exit $self->{errors}->{$self->{myerrors}->{$self->{global_status}}};
}

sub get_most_critical {
    my ($self, %options) = @_;
    my $current_status = 0; # For 'OK'

    foreach (@{$options{status}}) {
        if ($self->{myerrors_mask}->{uc($_)} > $current_status) {
            $current_status = $self->{myerrors_mask}->{uc($_)};
        }
    }
    return $self->{myerrors}->{$current_status};
}

sub get_litteral_status {
    my ($self, %options) = @_;

    return $self->{myerrors}->{$self->{global_status}};
}

sub is_status {
    my ($self, %options) = @_;
    # $options{value} = string status 
    # $options{litteral} = value is litteral
    # $options{compare} = string status 

    if (defined($options{litteral})) {
        my $value = defined($options{value}) ? $options{value} : $self->get_litteral_status();
    
        if (uc($value) eq uc($options{compare})) {
            return 1;
        }
        return 0;
    }

    my $value = defined($options{value}) ? $options{value} : $self->{global_status};
    my $dec_val = $self->{myerrors_mask}->{$value};
    my $lresult = $value & $dec_val;
    # Need to manage 0
    if ($lresult > 0 || ($dec_val == 0 && $value == 0)) {
        return 1;
    }
    return 0;
}

sub is_litteral_status {
    my ($self, %options) = @_;
    # $options{status} = string status

    if (defined($self->{errors}->{uc($options{status})})) {
        return 1;
    }

    return 0;
}

sub create_xml_document {
    my ($self) = @_;

    require XML::LibXML;
    $self->{is_output_xml} = 1;
    $self->{xml_output} = XML::LibXML::Document->new('1.0', 'utf-8');
}

sub plugin {
    my ($self, %options) = @_;
    # $options{name} = string name
    
    if (defined($options{name})) {
        $self->{plugin} = $options{name};
    }
    return $self->{plugin};
}

sub mode {
    my ($self, %options) = @_;
    # $options{name} = string name

    if (defined($options{name})) {
        $self->{mode} = $options{name};
    }
    return $self->{mode};
}

sub add_disco_format {
    my ($self, %options) = @_;

    push @{$self->{disco_elements}}, @{$options{elements}};
}

sub display_disco_format {
    my ($self, %options) = @_;
    
    $self->create_xml_document();
    
    my $root = $self->{xml_output}->createElement('data');
    $self->{xml_output}->setDocumentElement($root);

    foreach (@{$self->{disco_elements}}) {
        my $child = $self->{xml_output}->createElement("element");
        $child->appendText($_);
        $root->addChild($child);
    }

    print $self->{xml_output}->toString(1);
}

sub display_disco_show {
    my ($self, %options) = @_;
    
    $self->create_xml_document();
    
    my $root = $self->{xml_output}->createElement('data');
    $self->{xml_output}->setDocumentElement($root);

    foreach (@{$self->{disco_entries}}) {
        my $child = $self->{xml_output}->createElement("label");
        foreach my $key (keys %$_) {
            $child->setAttribute($key, $_->{$key});
        }
        $root->addChild($child);
    }

    print $self->{xml_output}->toString(1);
}

sub add_disco_entry {
    my ($self, %options) = @_;
    
    push @{$self->{disco_entries}}, {%options};
}

sub is_disco_format {
    my ($self) = @_;

    if (defined($self->{option_results}->{disco_format}) ) {
        return 1;
    }
    return 0;
}

sub is_disco_show {
    my ($self) = @_;

    if ( defined($self->{option_results}->{disco_show}) ) {
        return 1;
    }
    return 0;
}

1;

__END__

=head1 NAME

Output class

=head1 SYNOPSIS

-

=head1 OUTPUT OPTIONS

=over 8

=item B<--verbose>

Display long output.

=item B<--ignore-perfdata>

Don't display perfdata.

=item B<--opt-exit>

Exit code for an option error, usage (default: unknown).

=item B<--output-xml>

Display output in XML Format.

=item B<--disco-format>

Display discovery arguments (if the mode manages it).

=item B<--disco-show>

Display discovery values (if the mode manages it).

=head1 DESCRIPTION

B<output>.

=cut
