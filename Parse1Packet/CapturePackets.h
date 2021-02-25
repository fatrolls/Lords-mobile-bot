#pragma once

int		StartCapturePackets(int AutoPickAdapter);
void	StopCapturePackets();
int		PickAdapter(int AutoPickAdapter = -1);