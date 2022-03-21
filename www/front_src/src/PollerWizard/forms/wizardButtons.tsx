import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Button, CircularProgress } from '@mui/material';

import { labelPrevious, labelNext, labelApply } from '../translatedLabels';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';

interface Props {
  goToPreviousStep: () => void;
  loading: boolean;
  type: 'Next' | 'Apply';
}

const WizardButtons = ({
  goToPreviousStep,
  loading,
  type,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const label = type === 'Next' ? t(labelNext) : t(labelApply);
  const loadIcon =
    type === 'Apply' && loading ? <CircularProgress size={15} /> : null;

  return (
    <div className={classes.formButton}>
      <Button size="small" onClick={goToPreviousStep}>
        {t(labelPrevious)}
      </Button>
      <Button
        color="primary"
        disabled={loading}
        endIcon={loadIcon}
        size="small"
        type="submit"
        variant="contained"
      >
        {label}
      </Button>
    </div>
  );
};

export default WizardButtons;
