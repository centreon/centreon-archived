import { Box } from '@mui/material';

interface Props {
  color?: string;
  icon: JSX.Element;
}

const Chip = ({ icon, color }: Props): JSX.Element => {
  return (
    <Box
      sx={{
        ...(color && {
          color,
        }),
      }}
    >
      {icon}
    </Box>
  );
};

export default Chip;
