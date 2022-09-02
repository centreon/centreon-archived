import { useTheme } from '@mui/material';
import IconAcknowledge from '@mui/icons-material/Person';

import Chip from '.';

const AcknowledgeChip = (): JSX.Element => {
  const theme = useTheme();

  return (
    <Chip
      color={theme.palette.action.acknowledged}
      icon={<IconAcknowledge fontSize="small" />}
    />
  );
};

export default AcknowledgeChip;
